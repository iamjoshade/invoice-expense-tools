<?php

namespace Tests\Feature;

use App\Services\ExpenseSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExpenseFlowTest extends TestCase
{
   private function row(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Adobe Creative Cloud',
            'amount' => 19.99,
            'expense_date' => '2026-05-10',
            'category' => 'software-subscriptions',
            'payment_method' => 'bank-transfer',
            'description' => '',
        ], $overrides);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'currency' => 'GBP',
            'expenses' => [
                $this->row(),
                $this->row(['title' => 'Train to client', 'amount' => 5.01, 'category' => 'motor-travel']),
            ],
        ], $overrides);
    }

    private function storedSummary(): array
    {
        return (new ExpenseSummaryService())->build($this->payload());
    }

    public function test_the_form_loads(): void
    {
        $this->get(route('expenses.create'))
            ->assertOk()
            ->assertSee('Track your expenses');
    }

    public function test_a_valid_submission_is_stored_and_redirects_to_preview(): void
    {
        $this->post(route('expenses.store'), $this->payload())
            ->assertRedirect(route('expenses.preview'))
            ->assertSessionHas('expenses.total', 25.0)
            ->assertSessionHas('expenses.currency', 'GBP');
    }

    public function test_the_preview_shows_every_expense_and_the_category_summary(): void
    {
        $this->post(route('expenses.store'), $this->payload());

        $this->get(route('expenses.preview'))
            ->assertOk()
            ->assertSee('Adobe Creative Cloud')
            ->assertSee('Train to client')
            ->assertSee('Software &amp; Subscriptions', false)
            ->assertSee('Motor &amp; Travel', false)
            ->assertSee('£25.00');
    }

    public function test_the_preview_without_a_session_redirects_to_the_form(): void
    {
        $this->get(route('expenses.preview'))
            ->assertRedirect(route('expenses.create'))
            ->assertSessionHas('error');
    }

    public function test_editing_refills_every_row_from_the_session(): void
    {
        $this->withSession(['expenses' => $this->storedSummary()])
            ->get(route('expenses.create'))
            ->assertOk()
            ->assertSee('Adobe Creative Cloud')
            ->assertSee('Train to client');
    }

    public function test_user_text_is_escaped_on_the_preview(): void
    {
        $this->post(route('expenses.store'), $this->payload([
            'expenses' => [$this->row(['title' => '<script>alert(1)</script>'])],
        ]));

        $this->get(route('expenses.preview'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_required_fields_are_validated_per_row(): void
    {
        $this->post(route('expenses.store'), $this->payload([
            'expenses' => [$this->row(['title' => '', 'amount' => ''])],
        ]))->assertSessionHasErrors(['expenses.0.title', 'expenses.0.amount']);
    }

    public function test_at_least_one_expense_is_required(): void
    {
        $this->post(route('expenses.store'), $this->payload(['expenses' => []]))
            ->assertSessionHasErrors('expenses');
    }

    public function test_ten_expenses_are_allowed(): void
    {
        $this->post(route('expenses.store'), $this->payload([
            'expenses' => array_fill(0, 10, $this->row()),
        ]))->assertSessionHasNoErrors();
    }

    public function test_eleven_expenses_are_rejected(): void
    {
        $this->post(route('expenses.store'), $this->payload([
            'expenses' => array_fill(0, 11, $this->row()),
        ]))->assertSessionHasErrors('expenses');
    }

    public function test_an_unknown_category_is_rejected(): void
    {
        $this->post(route('expenses.store'), $this->payload([
            'expenses' => [$this->row(['category' => 'not-a-category'])],
        ]))->assertSessionHasErrors('expenses.0.category');
    }

    public function test_an_unknown_payment_method_is_rejected(): void
    {
        $this->post(route('expenses.store'), $this->payload([
            'expenses' => [$this->row(['payment_method' => 'bitcoin'])],
        ]))->assertSessionHasErrors('expenses.0.payment_method');
    }

    public function test_an_unknown_currency_is_rejected(): void
    {
        $this->post(route('expenses.store'), $this->payload(['currency' => 'XYZ']))
            ->assertSessionHasErrors('currency');
    }

    public function test_the_download_returns_a_csv(): void
    {
        $summary = $this->storedSummary();

        $response = $this->withSession(['expenses' => $summary])
            ->get(route('expenses.download'));

        $response->assertOk();
        $response->assertDownload($summary['reference'] . '.csv');
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Adobe Creative Cloud', $csv);
        $this->assertStringContainsString('Total,25.00', $csv);
    }

    public function test_the_download_without_a_session_redirects_to_the_form(): void
    {
        $this->get(route('expenses.download'))
            ->assertRedirect(route('expenses.create'))
            ->assertSessionHas('error');
    }
}
