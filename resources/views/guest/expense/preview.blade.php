@extends('layout.custom-layout')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="mb-4 overflow-hidden shadow-sm card">
                    <div class="p-4 card-body p-md-5">

                        <div class="mb-4 d-flex justify-content-between align-items-start">
                            <h4 class="mb-0 fw-bold">Expense summary</h4>
                            <div class="text-end">
                                <p class="mb-0 text-muted small">{{ $data['reference'] }}</p>
                                <p class="mb-0 text-muted small">{{ $data['created_at'] }}</p>
                            </div>
                        </div>

                        <div class="mb-4 table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Expense</th>
                                        <th>Category</th>
                                        <th>Payment method</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['expenses'] as $expense)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($expense['expense_date'])->format('d M Y') }}</td>
                                            <td>
                                                {{ $expense['title'] }}
                                                @if ($expense['description'])
                                                    <div class="text-muted small">{{ $expense['description'] }}</div>
                                                @endif
                                            </td>
                                            <td>{{ config("expenses.categories.{$expense['category']}", $expense['category']) }}
                                            </td>
                                            <td>{{ config("expenses.payment_methods.{$expense['payment_method']}", $expense['payment_method']) }}
                                            </td>
                                            <td class="text-end">
                                                {{ $data['currency_symbol'] }}{{ number_format($expense['amount'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-semibold">Total</td>
                                        <td class="text-end fw-bold">
                                            {{ $data['currency_symbol'] }}{{ number_format($data['total'], 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <h6 class="mb-2">By category</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-center" style="width:80px;">Items</th>
                                        <th class="text-end" style="width:140px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['by_category'] as $row)
                                        <tr>
                                            <td>{{ config("expenses.categories.{$row['category']}", $row['category']) }}
                                            </td>
                                            <td class="text-center">{{ $row['count'] }}</td>
                                            <td class="text-end">
                                                {{ $data['currency_symbol'] }}{{ number_format($row['total'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="flex-wrap gap-3 mb-5 d-flex align-items-center justify-content-between">
                    <a href="{{ route('expenses.create') }}" class="btn btn-outline-primary">Edit expenses</a>
                    <a href="{{ route('expenses.download') }}" class="btn btn-primary">Download CSV</a>
                </div>

            </div>
        </div>
    </div>
@endsection
