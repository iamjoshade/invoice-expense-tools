<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpensesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'currency' => ['required', Rule::in(array_keys(config('currencies')))],
            'expenses' => ['required', 'array', 'min:1', 'max:' . config('expenses.max_expenses')],
            'expenses.*.title' => ['required', 'string', 'max:255'],
            'expenses.*.amount' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'expenses.*.expense_date' => ['required', 'date'],
            'expenses.*.category' => ['required', Rule::in(array_keys(config('expenses.categories')))],
            'expenses.*.payment_method' => ['required', Rule::in(array_keys(config('expenses.payment_methods')))],
            'expenses.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
