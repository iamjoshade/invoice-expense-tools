<?php

namespace Tests\Unit;

use App\Services\ExpenseSummaryService;
use PHPUnit\Framework\Attributes\DataProvider;
//use PHPUnit\Framework\TestCase;
use Tests\TestCase;

class ExpenseSummaryServiceTest extends TestCase
{

   private function data(array $overrides = []): array
    {
        return array_merge([
            'currency' => 'GBP',
            'expenses' => [
                [
                    'title' => 'Adobe Creative Cloud',
                    'amount' => '10.10',
                    'expense_date' => '2026-05-10',
                    'category' => 'software-subscriptions',
                    'payment_method' => 'direct-debit',
                    'description' => null,
                ],
                [
                    'title' => 'Notion',
                    'amount' => '20.20',
                    'expense_date' => '2026-05-12',
                    'category' => 'software-subscriptions',
                    'payment_method' => 'credit-card',
                    'description' => 'Team plan',
                ],
                [
                    'title' => 'Train to client',
                    'amount' => '19.70',
                    'expense_date' => '2026-05-14',
                    'category' => 'motor-travel',
                    'payment_method' => 'debit-card',
                    'description' => '',
                ],
            ],
        ], $overrides);
    }

    public function test_it_calculates_the_total(): void
    {
        $summary = (new ExpenseSummaryService())->build($this->data());

        $this->assertSame(50.0, $summary['total']);
    }

    public function test_it_groups_totals_by_category(): void
    {
        $summary = (new ExpenseSummaryService())->build($this->data());

        $byCategory = collect($summary['by_category'])->keyBy('category');

        $this->assertSame(2, $byCategory['software-subscriptions']['count']);
        $this->assertSame(30.3, $byCategory['software-subscriptions']['total']);
        $this->assertSame(1, $byCategory['motor-travel']['count']);
        $this->assertSame(19.7, $byCategory['motor-travel']['total']);
    }

    public function test_it_defaults_a_missing_description_to_an_empty_string(): void
    {
        $summary = (new ExpenseSummaryService())->build($this->data());

        $this->assertSame('', $summary['expenses'][0]['description']);
    }

    public function test_it_looks_up_the_currency_symbol_and_makes_a_reference(): void
    {
        $summary = (new ExpenseSummaryService())->build($this->data(['currency' => 'EUR']));

        $this->assertSame('€', $summary['currency_symbol']);
        $this->assertStringStartsWith('EXP-', $summary['reference']);
    }

    /**
     * @dataProvider taxYearProvider
     */

    #[DataProvider('taxYearProvider')]
    public function test_it_works_out_the_uk_tax_year(string $date, string $expected): void
    {
        $this->assertSame($expected, (new ExpenseSummaryService())->taxYear($date));
    }

    public static function taxYearProvider(): array
    {
        return [
            'last day of the old tax year' => ['2026-04-05', '2025/26'],
            'first day of the new tax year' => ['2026-04-06', '2026/27'],
            'start of January' => ['2026-01-15', '2025/26'],
            'end of March' => ['2026-03-31', '2025/26'],
            'late April' => ['2026-04-30', '2026/27'],
            'end of December' => ['2026-12-31', '2026/27'],
        ];
    }

    public function test_the_csv_has_headers_rows_and_a_total(): void
    {
        $service = new ExpenseSummaryService();
        $rows = $this->csvRows($service, $service->build($this->data()));

        $this->assertSame(
            ['Expense name', 'Amount', 'Date', 'Category', 'Payment method', 'Description', 'Tax year'],
            $rows[0]
        );
        $this->assertSame(
            ['Adobe Creative Cloud', '10.10', '2026-05-10', 'Software & Subscriptions', 'Direct Debit', '', '2026/27'],
            $rows[1]
        );
        $this->assertSame(['Total', '50.00', '', '', '', '', ''], end($rows));
    }

    public function test_the_csv_neutralises_spreadsheet_formulas(): void
    {
        $service = new ExpenseSummaryService();
        $data = $this->data();
        $data['expenses'][0]['title'] = '=SUM(A1:A9)';
        $data['expenses'][1]['description'] = '@cmd';

        $rows = $this->csvRows($service, $service->build($data));

        $this->assertSame("'=SUM(A1:A9)", $rows[1][0]);
        $this->assertSame("'@cmd", $rows[2][5]);
    }

    private function csvRows(ExpenseSummaryService $service, array $summary): array
    {
        $handle = fopen('php://memory', 'w+');
        $service->writeCsv($handle, $summary);
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        // Strip the byte order mark so the first header parses cleanly.
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);

        return array_map(
            fn ($line) => str_getcsv($line, ',', '"', '\\'),
            preg_split('/\r?\n/', trim($csv))
        );
    }

}
