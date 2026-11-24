<?php

// app/Services/ExpenseSessionService.php
namespace App\Services;

use Illuminate\Support\Str;

class ExpenseSessionService
{
    private const KEY = 'expenses';

    public function all(): array
    {
        return session(self::KEY, []);
    }

    public function isFull(): bool
    {
        return count($this->all()) >= config('expenses.max_expenses');
    }

    public function add(array $data): bool
    {
        if ($this->isFull()) {
            return false;
        }

        $expenses = $this->all();
        $expenses[] = [
            'id' => (string) Str::uuid(),
            'date' => $data['date'],
            'description' => $data['description'],
            'amount' => round((float) $data['amount'], 2),
            'category' => $data['category'],
            'payment_method' => $data['payment_method'],
        ];

        session([self::KEY => $expenses]);

        return true;
    }

    public function remove(string $id): void
    {
        $remaining = array_values(array_filter(
            $this->all(),
            fn ($expense) => $expense['id'] !== $id
        ));

        session([self::KEY => $remaining]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function total(): float
    {
        return round(collect($this->all())->sum('amount'), 2);
    }

    public function writeCsv($handle): void
    {
        fputcsv($handle, ['Date', 'Description', 'Amount', 'Category', 'Payment method']);

        foreach ($this->all() as $expense) {
            fputcsv($handle, [
                $expense['date'],
                $this->safeCell($expense['description']),
                number_format($expense['amount'], 2, '.', ''),
                config("expenses.categories.{$expense['category']}"),
                config("expenses.payment_methods.{$expense['payment_method']}"),
            ]);
        }
    }

    // Stops spreadsheet apps treating user text as a formula.
    private function safeCell(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}
