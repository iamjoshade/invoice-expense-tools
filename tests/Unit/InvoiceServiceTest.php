<?php

namespace Tests\Unit;

use App\Services\InvoiceService;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    private function data(array $overrides = []): array
    {
        return array_merge([
            'your_name' => 'Jane Smith Consulting',
            'client_name' => 'Acme Ltd',
            'invoice_date' => '2026-11-01',
            'due_date' => '2026-12-01',
            'currency' => 'GBP',
            'terms' => null,
            'items' => [
                ['description' => 'Design work', 'quantity' => '3', 'unit_price' => '19.99'],
                ['description' => 'Hosting', 'quantity' => '0.5', 'unit_price' => '10'],
            ],
        ], $overrides);
    }

    public function test_it_calculates_line_totals_and_subtotal(): void
    {
        $invoice = (new InvoiceService())->build($this->data());

        $this->assertSame(59.97, $invoice['items'][0]['total']);
        $this->assertSame(5.0, $invoice['items'][1]['total']);
        $this->assertSame(64.97, $invoice['subtotal']);
    }

    public function test_it_casts_string_input_to_numbers(): void
    {
        $invoice = (new InvoiceService())->build($this->data());

        $this->assertSame(3.0, $invoice['items'][0]['quantity']);
        $this->assertSame(19.99, $invoice['items'][0]['unit_price']);
    }

    public function test_it_looks_up_the_symbol_from_config(): void
    {
        $gbp = (new InvoiceService())->build($this->data(['currency' => 'GBP']));
        $usd = (new InvoiceService())->build($this->data(['currency' => 'USD']));

        $this->assertSame('£', $gbp['currency_symbol']);
        $this->assertSame('$', $usd['currency_symbol']);
    }

    public function test_it_generates_a_reference_and_defaults_empty_terms(): void
    {
        $invoice = (new InvoiceService())->build($this->data());

        $this->assertStringStartsWith('INV-', $invoice['reference']);
        $this->assertSame('', $invoice['terms']);
    }
}