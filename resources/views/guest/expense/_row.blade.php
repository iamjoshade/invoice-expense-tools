<div class="p-4 expense-row border-bottom">
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 text-muted small expense-number">EXPENSE</h6>
        @if ($removable)
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove
                aria-label="Remove expense">&times;</button>
        @endif
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Expense name <span class="text-danger">*</span></label>
            <input type="text" name="expenses[{{ $index }}][title]"
                class="form-control @error("expenses.$index.title") is-invalid @enderror"
                placeholder="e.g. Adobe Creative Cloud" required value="{{ $expense['title'] ?? '' }}">
            @error("expenses.$index.title")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Amount <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text currency-symbol-display">£</span>
                <input type="number" name="expenses[{{ $index }}][amount]"
                    class="form-control expense-amount @error("expenses.$index.amount") is-invalid @enderror"
                    placeholder="0.00" step="0.01" min="0.01" required value="{{ $expense['amount'] ?? '' }}">
                @error("expenses.$index.amount")
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="expenses[{{ $index }}][expense_date]"
                class="form-control @error("expenses.$index.expense_date") is-invalid @enderror" required
                value="{{ $expense['expense_date'] ?? date('Y-m-d') }}">
            @error("expenses.$index.expense_date")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="expenses[{{ $index }}][category]"
                class="form-select @error("expenses.$index.category") is-invalid @enderror" required>
                <option value="">Select category</option>
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}" @selected(($expense['category'] ?? '') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error("expenses.$index.category")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Payment method <span class="text-danger">*</span></label>
            <select name="expenses[{{ $index }}][payment_method]"
                class="form-select @error("expenses.$index.payment_method") is-invalid @enderror" required>
                <option value="">Select method</option>
                @foreach ($paymentMethods as $value => $label)
                    <option value="{{ $value }}" @selected(($expense['payment_method'] ?? '') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error("expenses.$index.payment_method")
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Description <span class="text-muted small">(optional)</span></label>
            <input type="text" name="expenses[{{ $index }}][description]" class="form-control"
                placeholder="Brief description" value="{{ $expense['description'] ?? '' }}">
        </div>
    </div>
</div>
