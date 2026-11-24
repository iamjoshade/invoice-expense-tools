@extends('layout.custom-layout')

@section('content')
    <section class="py-5 section-bg">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">

                    <div class="mb-4 text-center">
                        <h1 class="h3 fw-bold">Track your expenses</h1>
                        <p class="text-muted">Add your expenses, preview a summary and download it as a CSV.</p>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $rows = old('expenses', $existing['expenses'] ?? [[]]);
                        $nextIndex = max(array_keys($rows)) + 1;
                        $selectedCurrency = old('currency', $existing['currency'] ?? 'GBP');
                    @endphp

                    <form action="{{ route('expenses.store') }}" method="POST" id="expenseForm"
                        data-next-index="{{ $nextIndex }}" data-max="{{ $max }}">
                        @csrf

                        <div class="mb-4 card">
                            <div class="card-header">
                                <h6 class="mb-0">Currency</h6>
                            </div>
                            <div class="card-body">
                                <select name="currency" id="currencySelect"
                                    class="form-select @error('currency') is-invalid @enderror" required>
                                    @foreach ($currencies as $code => $currency)
                                        <option value="{{ $code }}" data-symbol="{{ $currency['symbol'] }}"
                                            @selected($code === $selectedCurrency)>
                                            {{ $code }} - {{ $currency['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="mb-0">Expenses</h6>
                                <span class="text-muted small">Add up to {{ $max }} expenses</span>
                            </div>
                            <div class="p-0 card-body" id="expensesContainer">
                                @if ($errors->has('expenses'))
                                    <div class="m-3 alert alert-danger">{{ $errors->first('expenses') }}</div>
                                @endif
                                @foreach ($rows as $index => $expense)
                                    @include('guest.expense._row', [
                                        'index' => $index,
                                        'expense' => $expense,
                                        'removable' => !$loop->first,
                                    ])
                                @endforeach
                            </div>
                            <div class="p-3 border-top d-flex align-items-center justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addExpense">
                                    + Add another expense
                                </button>
                                <div class="text-end">
                                    <span class="text-muted me-2">Total:</span>
                                    <strong id="totalDisplay">£0.00</strong>
                                </div>
                            </div>
                        </div>

                        <div class="gap-3 mb-5 d-flex flex-column flex-md-row align-items-center justify-content-between">
                            <p class="mb-0 text-muted small">
                                Nothing is saved on a server. Your expenses live in your browser session.
                            </p>
                            <button type="submit" class="px-4 py-3 btn btn-primary">Preview summary</button>
                        </div>
                    </form>

                    <hr class="mb-0">
                    <div class="mt-4 text-center text-muted small">
                        Also try the <a href="{{ route('invoice.create') }}">invoice generator</a>.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <template id="expenseTemplate">
        @include('guest.expense._row', ['index' => '__INDEX__', 'expense' => [], 'removable' => true])
    </template>

    <script>
        (function() {
            const form = document.getElementById('expenseForm');
            const container = document.getElementById('expensesContainer');
            const template = document.getElementById('expenseTemplate').innerHTML;
            const select = document.getElementById('currencySelect');
            const totalEl = document.getElementById('totalDisplay');
            const addBtn = document.getElementById('addExpense');
            const max = parseInt(form.dataset.max, 10);
            let nextIndex = parseInt(form.dataset.nextIndex, 10);

            const symbol = () => select.selectedOptions[0].dataset.symbol;

            function refresh() {
                const rows = container.querySelectorAll('.expense-row');
                let cents = 0;
                rows.forEach(function(row, i) {
                    row.querySelector('.expense-number').textContent = 'EXPENSE #' + (i + 1);
                    row.querySelector('.currency-symbol-display').textContent = symbol();
                    cents += Math.round((parseFloat(row.querySelector('.expense-amount').value) || 0) * 100);
                });
                totalEl.textContent = symbol() + (cents / 100).toFixed(2);
                addBtn.disabled = rows.length >= max;
            }

            addBtn.addEventListener('click', function() {
                if (container.querySelectorAll('.expense-row').length >= max) return;
                container.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', nextIndex));
                nextIndex++;
                refresh();
            });

            container.addEventListener('input', refresh);
            container.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-remove]');
                if (!btn) return;
                btn.closest('.expense-row').remove();
                refresh();
            });
            select.addEventListener('change', refresh);

            refresh();
        })();
    </script>
@endsection
