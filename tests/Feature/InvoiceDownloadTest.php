<?php

namespace Tests\Feature;

use App\Services\InvoicePdfService;
use RuntimeException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InvoiceDownloadTest extends TestCase
{
    private function sampleInvoice(): array
    {
        return [
            'reference' => 'INV-TEST01',
            'your_name' => 'Jane Smith Consulting',
            'client_name' => 'Acme Ltd',
            'invoice_date' => '2026-11-01',
            'due_date' => '2026-12-01',
            'currency' => 'GBP',
            'currency_symbol' => '£',
            'terms' => '',
            'items' => [
                ['description' => 'Consulting', 'quantity' => 2.0, 'unit_price' => 50.0, 'total' => 100.0],
            ],
            'subtotal' => 100.0,
        ];
    }

    public function test_download_returns_a_pdf(): void
    {
        $this->mock(InvoicePdfService::class, function ($mock) {
            $mock->shouldReceive('render')->once()->andReturn('%PDF-fake');
        });

        $response = $this->withSession(['invoice' => $this->sampleInvoice()])
            ->get(route('invoice.download'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertDownload('INV-TEST01.pdf');
    }

     public function test_download_without_a_session_redirects_to_the_form(): void
    {
        $this->get(route('invoice.download'))
            ->assertRedirect(route('invoice.create'))
            ->assertSessionHas('error');
    }

    public function test_download_shows_an_error_when_pdf_generation_fails(): void
    {
        $this->mock(InvoicePdfService::class, function ($mock) {
            $mock->shouldReceive('render')->andThrow(new RuntimeException('Chrome missing'));
        });

        $this->withSession(['invoice' => $this->sampleInvoice()])
            ->get(route('invoice.download'))
            ->assertRedirect(route('invoice.preview'))
            ->assertSessionHas('error');
    }
}
