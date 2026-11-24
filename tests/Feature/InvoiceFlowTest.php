<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvoiceFlowTest extends TestCase
{
     private function payload(array $overrides = []): array
    {
        return array_merge([
            'your_name' => 'Jane Smith Consulting',
            'client_name' => 'Acme Ltd',
            'invoice_date' => '2026-11-01',
            'due_date' => '2026-12-01',
            'currency' => 'GBP',
            'terms' => 'Payment within 30 days.',
            'items' => [
                ['description' => 'Consulting', 'quantity' => 2, 'unit_price' => 50],
            ],
        ], $overrides);
    }

    public function test_the_form_loads(): void
    {
        $this->get(route('invoice.create'))
            ->assertOk()
            ->assertSee('Create a free invoice');
    }

    public function test_a_valid_invoice_is_stored_and_redirects_to_preview(): void
    {
        $this->post(route('invoice.store'), $this->payload())
            ->assertRedirect(route('invoice.preview'))
            ->assertSessionHas('invoice.client_name', 'Acme Ltd')
            ->assertSessionHas('invoice.subtotal', 100.0);
    }

    public function test_the_preview_shows_the_invoice(): void
    {
        $this->post(route('invoice.store'), $this->payload());

        $this->get(route('invoice.preview'))
            ->assertOk()
            ->assertSee('Acme Ltd')
            ->assertSee('Jane Smith Consulting')
            ->assertSee('£100.00');
    }

    public function test_the_preview_without_a_session_redirects_to_the_form(): void
    {
        $this->get(route('invoice.preview'))
            ->assertRedirect(route('invoice.create'))
            ->assertSessionHas('error');
    }

    public function test_user_text_is_escaped_on_the_preview(): void
    {
        $this->post(route('invoice.store'), $this->payload([
            'client_name' => '<script>alert(1)</script>',
        ]));

        $this->get(route('invoice.preview'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->post(route('invoice.store'), $this->payload(['client_name' => '', 'items' => []]))
            ->assertSessionHasErrors(['client_name', 'items']);
    }

    public function test_due_date_cannot_be_before_the_invoice_date(): void
    {
        $this->post(route('invoice.store'), $this->payload([
            'invoice_date' => '2026-12-01',
            'due_date' => '2026-11-01',
        ]))->assertSessionHasErrors('due_date');
    }

    public function test_an_unknown_currency_is_rejected(): void
    {
        $this->post(route('invoice.store'), $this->payload(['currency' => 'XYZ']))
            ->assertSessionHasErrors('currency');
    }

    public function test_more_than_twenty_items_are_rejected(): void
    {
        $items = array_fill(0, 21, ['description' => 'Item', 'quantity' => 1, 'unit_price' => 1]);

        $this->post(route('invoice.store'), $this->payload(['items' => $items]))
            ->assertSessionHasErrors('items');
    }
}