<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice['reference'] }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #222; margin: 0; }
        h1 { margin: 0; font-size: 26px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #222; color: #fff; text-align: left; padding: 8px; }
        td { border-bottom: 1px solid #ddd; padding: 8px; }
        .right { text-align: right; }
        .total td { font-weight: bold; border-bottom: none; }
    </style>
</head>
<body>
    <div class="row">
        <strong style="font-size:18px;">{{ $invoice['your_name'] }}</strong>
        <div class="right">
            <h1>INVOICE</h1>
            <div class="muted">{{ $invoice['reference'] }}</div>
        </div>
    </div>

    <div class="row">
        <div>
            <div class="muted">Billed to</div>
            <strong>{{ $invoice['client_name'] }}</strong>
        </div>
        <div class="right">
            <div>Invoice date: {{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('d M Y') }}</div>
            <div>Due date: {{ \Carbon\Carbon::parse($invoice['due_date'])->format('d M Y') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice['items'] as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="right">{{ rtrim(rtrim(number_format($item['quantity'], 2), '0'), '.') }}</td>
                    <td class="right">{{ $invoice['currency_symbol'] }}{{ number_format($item['unit_price'], 2) }}</td>
                    <td class="right">{{ $invoice['currency_symbol'] }}{{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3" class="right">Total</td>
                <td class="right">{{ $invoice['currency_symbol'] }}{{ number_format($invoice['subtotal'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($invoice['terms'])
        <p class="muted" style="margin-top:24px; white-space: pre-line;">{{ $invoice['terms'] }}</p>
    @endif
</body>
</html>
