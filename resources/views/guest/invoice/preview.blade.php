@extends('layout.custom-layout')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="mb-4 overflow-hidden shadow-sm card">
                    <div class="p-4 card-body p-md-5">

                        <div class="mb-4 d-flex justify-content-between align-items-start">
                            <h4 class="mb-0 fw-bold">{{ $invoice['your_name'] }}</h4>
                            <div class="text-end">
                                <h3 class="mb-1 fw-bold">INVOICE</h3>
                                <p class="mb-0 text-muted small">{{ $invoice['reference'] }}</p>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4 row">
                            <div class="col-md-6">
                                <p class="mb-1 text-muted small">Billed to</p>
                                <h6 class="mb-0">{{ $invoice['client_name'] }}</h6>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="mb-1 text-muted small">
                                    Invoice date:
                                    <strong>{{ \Carbon\Carbon::parse($invoice['invoice_date'])->format('d M Y') }}</strong>
                                </p>
                                <p class="mb-0 text-muted small">
                                    Due date:
                                    <strong>{{ \Carbon\Carbon::parse($invoice['due_date'])->format('d M Y') }}</strong>
                                </p>
                            </div>
                        </div>

                        <div class="mb-4 table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-center" style="width:80px;">Qty</th>
                                        <th class="text-end" style="width:120px;">Unit price</th>
                                        <th class="text-end" style="width:120px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice['items'] as $item)
                                        <tr>
                                            <td>{{ $item['description'] }}</td>
                                            <td class="text-center">
                                                {{ rtrim(rtrim(number_format($item['quantity'], 2), '0'), '.') }}
                                            </td>
                                            <td class="text-end">
                                                {{ $invoice['currency_symbol'] }}{{ number_format($item['unit_price'], 2) }}
                                            </td>
                                            <td class="text-end">
                                                {{ $invoice['currency_symbol'] }}{{ number_format($item['total'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-semibold">Total</td>
                                        <td class="text-end fw-bold">
                                            {{ $invoice['currency_symbol'] }}{{ number_format($invoice['subtotal'], 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if ($invoice['terms'])
                            <div>
                                <p class="mb-1 text-muted small">Notes</p>
                                <p class="small" style="white-space: pre-line;">{{ $invoice['terms'] }}</p>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="flex-wrap gap-3 mb-5 d-flex align-items-center justify-content-between">
                    <a href="{{ route('invoice.create') }}" class="btn btn-outline-primary">Edit invoice</a>
                    <a href="{{ route('invoice.download') }}" class="btn btn-primary">Download PDF</a>
                </div>

            </div>
        </div>
    </div>
@endsection
