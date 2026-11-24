<?php

// app/Services/InvoiceService.php
namespace App\Services;

use Illuminate\Support\Str;

class InvoiceService
{
    public function build(array $data): array
    {
        $items = collect($data['items'])->map(function ($item) {
            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            return [
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => round($quantity * $unitPrice, 2),
            ];
        })->values()->all();

        return [
            'reference' => 'INV-' . strtoupper(Str::random(6)),
            'your_name' => $data['your_name'],
            'client_name' => $data['client_name'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'currency' => $data['currency'],
            'currency_symbol' => config("currencies.{$data['currency']}.symbol"),
            'terms' => $data['terms'] ?? '',
            'items' => $items,
            'subtotal' => round(collect($items)->sum('total'), 2),
        ];
    }
}
