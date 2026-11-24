@extends('layout.custom-layout')

@section('content')
    <section class="py-5 section-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">

                    <div class="mb-4 text-center">
                        <h1 class="h3 fw-bold">Create a free invoice</h1>
                        <p class="text-muted">Fill in the details, preview and download as a PDF.</p>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @php
                        $items = old(
                            'items',
                            $existing['items'] ?? [['description' => '', 'quantity' => 1, 'unit_price' => '']],
                        );
                        $selectedCurrency = old('currency', $existing['currency'] ?? 'GBP');
                    @endphp

                    <form action="{{ route('invoice.store') }}" method="POST" id="invoiceForm"
                        data-row-index="{{ count($items) }}">
                        @csrf

                        <div class="mb-4 card">
                            <div class="card-header">
                                <h6 class="mb-0">Your details</h6>
                            </div>
                            <div class="card-body">
                                <label class="form-label">Your name or business name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="your_name"
                                    class="form-control @error('your_name') is-invalid @enderror"
                                    value="{{ old('your_name', $existing['your_name'] ?? '') }}"
                                    placeholder="e.g. Jane Smith Consulting" required>
                                @error('your_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 card">
                            <div class="card-header">
                                <h6 class="mb-0">Client details</h6>
                            </div>
                            <div class="card-body">
                                <label class="form-label">Client name <span class="text-danger">*</span></label>
                                <input type="text" name="client_name"
                                    class="form-control @error('client_name') is-invalid @enderror"
                                    value="{{ old('client_name', $existing['client_name'] ?? '') }}"
                                    placeholder="e.g. Acme Ltd" required>
                                @error('client_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 card">
                            <div class="card-header">
                                <h6 class="mb-0">Invoice details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Invoice date <span class="text-danger">*</span></label>
                                        <input type="date" name="invoice_date"
                                            class="form-control @error('invoice_date') is-invalid @enderror"
                                            value="{{ old('invoice_date', $existing['invoice_date'] ?? date('Y-m-d')) }}"
                                            required>
                                        @error('invoice_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Due date <span class="text-danger">*</span></label>
                                        <input type="date" name="due_date"
                                            class="form-control @error('due_date') is-invalid @enderror"
                                            value="{{ old('due_date', $existing['due_date'] ?? date('Y-m-d', strtotime('+30 days'))) }}"
                                            required>
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                                        <select name="currency" id="currencySelect"
                                            class="form-select @error('currency') is-invalid @enderror" required>
                                            @foreach ($currencies as $code => $currency)
                                                <option value="{{ $code }}"
                                                    data-symbol="{{ $currency['symbol'] }}" @selected($code === $selectedCurrency)>
                                                    {{ $code }} - {{ $currency['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('currency')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 card">
                            <div class="card-header">
                                <h6 class="mb-0">Items</h6>
                            </div>
                            <div class="p-0 card-body">
                                @if ($errors->has('items') || $errors->has('items.*'))
                                    <div class="m-3 alert alert-danger">Please check the items below.</div>
                                @endif
                                <div class="table-responsive">
                                    <table class="table mb-0" id="itemsTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Description</th>
                                                <th style="width:100px;">Qty</th>
                                                <th style="width:120px;">Unit price</th>
                                                <th style="width:120px;">Total</th>
                                                <th style="width:40px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemsBody">
                                            @foreach ($items as $index => $item)
                                                <tr>
                                                    <td data-label="Description">
                                                        <input type="text"
                                                            name="items[{{ $index }}][description]"
                                                            class="form-control" value="{{ $item['description'] ?? '' }}"
                                                            required>
                                                    </td>
                                                    <td data-label="Quantity">
                                                        <input type="number" name="items[{{ $index }}][quantity]"
                                                            class="form-control qty" value="{{ $item['quantity'] ?? 1 }}"
                                                            min="0.01" step="0.01" required>
                                                    </td>
                                                    <td data-label="Unit price">
                                                        <input type="number" name="items[{{ $index }}][unit_price]"
                                                            class="form-control price"
                                                            value="{{ $item['unit_price'] ?? '' }}" min="0"
                                                            step="0.01" placeholder="0.00" required>
                                                    </td>
                                                    <td data-label="Total">
                                                        <input type="text" class="form-control row-total" readonly
                                                            tabindex="-1" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        @if ($index > 0)
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                data-remove aria-label="Remove item">&times;</button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-3 border-top d-flex align-items-center justify-content-between">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItem">
                                        + Add item
                                    </button>
                                    <div class="text-end">
                                        <span class="text-muted me-2">Subtotal:</span>
                                        <strong id="subtotalDisplay">£0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 card">
                            <div class="card-header">
                                <h6 class="mb-0">Notes <span class="text-muted small">(optional)</span></h6>
                            </div>
                            <div class="card-body">
                                <textarea name="terms" class="form-control @error('terms') is-invalid @enderror" rows="3"
                                    placeholder="Payment terms, a thank you note, or anything else the client should know.">{{ old('terms', $existing['terms'] ?? '') }}</textarea>
                                @error('terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="gap-3 mb-5 d-flex flex-column flex-md-row align-items-center justify-content-between">
                            <p class="mb-0 text-muted small">
                                Nothing is saved on a server. Your invoice lives in your browser session.
                            </p>
                            <button type="submit" class="px-4 py-3 btn btn-primary">Preview invoice</button>
                        </div>
                    </form>

                    <hr class="mb-0">
                    <div class="mt-4 text-center text-muted small">
                        Also try the <a href="{{ route('expenses.create') }}">expense tracker</a>.
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function() {
            const form = document.getElementById('invoiceForm');
            const body = document.getElementById('itemsBody');
            const select = document.getElementById('currencySelect');
            const subtotalEl = document.getElementById('subtotalDisplay');
            const MAX_ROWS = 20;
            let rowIndex = parseInt(form.dataset.rowIndex, 10);

            const symbol = () => select.selectedOptions[0].dataset.symbol;

            function updateTotals() {
                let subtotal = 0;
                body.querySelectorAll('tr').forEach(function(row) {
                    const qty = parseFloat(row.querySelector('.qty').value) || 0;
                    const price = parseFloat(row.querySelector('.price').value) || 0;
                    const total = Math.round(qty * price * 100) / 100;
                    row.querySelector('.row-total').value = total ? total.toFixed(2) : '';
                    subtotal += total;
                });
                subtotalEl.textContent = symbol() + subtotal.toFixed(2);
            }

            document.getElementById('addItem').addEventListener('click', function() {
                if (body.querySelectorAll('tr').length >= MAX_ROWS) return;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td data-label="Description">
                        <input type="text" name="items[${rowIndex}][description]" class="form-control" required>
                    </td>
                    <td data-label="Quantity">
                        <input type="number" name="items[${rowIndex}][quantity]" class="form-control qty"
                            value="1" min="0.01" step="0.01" required>
                    </td>
                    <td data-label="Unit price">
                        <input type="number" name="items[${rowIndex}][unit_price]" class="form-control price"
                            min="0" step="0.01" placeholder="0.00" required>
                    </td>
                    <td data-label="Total">
                        <input type="text" class="form-control row-total" readonly tabindex="-1" placeholder="0.00">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove
                            aria-label="Remove item">&times;</button>
                    </td>`;
                body.appendChild(tr);
                rowIndex++;
            });

            body.addEventListener('input', updateTotals);
            body.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-remove]');
                if (!btn) return;
                btn.closest('tr').remove();
                updateTotals();
            });
            select.addEventListener('change', updateTotals);

            updateTotals();
        })();
    </script>
@endpush
