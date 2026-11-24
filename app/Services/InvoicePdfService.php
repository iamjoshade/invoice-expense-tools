<?php

// app/Services/InvoicePdfService.php
namespace App\Services;

use Spatie\Browsershot\Browsershot;

class InvoicePdfService
{
     public function render(array $invoice): string
    {
        $html = view('guest.invoice.pdf', compact('invoice'))->render();

        return Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->disableJavascript()
            ->pdf();
    }
}
