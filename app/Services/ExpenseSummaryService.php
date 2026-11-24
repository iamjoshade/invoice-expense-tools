<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class ExpenseSummaryService
{
    public function build(array $data): array
    {
        $expenses = collect($data['expenses'])->map(fn ($expense) => [
            'title' => $expense['title'],
            'amount' => round((float) $expense['amount'], 2),
            'expense_date' => $expense['expense_date'],
            'category' => $expense['category'],
            'payment_method' => $expense['payment_method'],
            'description' => $expense['description'] ?? '',
            'tax_year' => $this->taxYear($expense['expense_date']),
        ])->values()->all();

        $byCategory = collect($expenses)->groupBy('category')->map(fn ($group, $category) => [
            'category' => $category,
            'count' => $group->count(),
            'total' => round($group->sum('amount'), 2),
        ])->values()->all();

        return [
            'reference' => 'EXP-' . strtoupper(Str::random(6)),
            'currency' => $data['currency'],
            'currency_symbol' => config("currencies.{$data['currency']}.symbol"),
            'expenses' => $expenses,
            'total' => round(collect($expenses)->sum('amount'), 2),
            'by_category' => $byCategory,
            'created_at' => now()->format('d M Y'),
        ];
    }

    // UK tax years run from 6 April to 5 April.
    public function taxYear(string $date): string
    {
        $d = Carbon::parse($date);
        $startYear = ($d->month > 4 || ($d->month === 4 && $d->day >= 6)) ? $d->year : $d->year - 1;

        return $startYear . '/' . substr((string) ($startYear + 1), 2);
    }

    public function writeCsv($handle, array $data): void
    {
        // BOM so Excel reads £ and accented characters correctly.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ['Expense name', 'Amount', 'Date', 'Category', 'Payment method', 'Description', 'Tax year']);

        foreach ($data['expenses'] as $expense) {
            fputcsv($handle, [
                $this->safeCell($expense['title']),
                number_format($expense['amount'], 2, '.', ''),
                $expense['expense_date'],
                config("expenses.categories.{$expense['category']}", $expense['category']),
                config("expenses.payment_methods.{$expense['payment_method']}", $expense['payment_method']),
                $this->safeCell($expense['description']),
                $expense['tax_year'],
            ]);
        }

        fputcsv($handle, ['Total', number_format($data['total'], 2, '.', ''), '', '', '', '', '']);
    }

    // Stops spreadsheet apps treating user text as a formula.
    private function safeCell(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
