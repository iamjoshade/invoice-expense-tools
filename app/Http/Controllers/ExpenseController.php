<?php

// app/Http/Controllers/ExpenseController.php
namespace App\Http\Controllers;



use App\Http\Requests\StoreExpensesRequest;
use App\Services\ExpenseSessionService;
use App\Services\ExpenseSummaryService;

class ExpenseController extends Controller
{
    public function index(ExpenseSessionService $expenses)
    {
        return view('guest.expense.index', [
            'expenses' => $expenses->all(),
            'total' => $expenses->total(),
            'isFull' => $expenses->isFull(),
            'categories' => config('expenses.categories'),
            'paymentMethods' => config('expenses.payment_methods'),
            'max' => config('expenses.max_records'),
        ]);
    }

     public function create()
    {
        return view('guest.expense.create', [
            'currencies' => config('currencies'),
            'categories' => config('expenses.categories'),
            'paymentMethods' => config('expenses.payment_methods'),
            'max' => config('expenses.max_expenses'),
            'existing' => session('expenses'),
        ]);
    }

   public function store(StoreExpensesRequest $request, ExpenseSummaryService $service)
    {
        session(['expenses' => $service->build($request->validated())]);

        return redirect()->route('expenses.preview');
    }

    public function destroy(string $id, ExpenseSessionService $expenses)
    {
        $expenses->remove($id);

        return redirect()->route('expenses.index');
    }

    public function clear(ExpenseSessionService $expenses)
    {
        $expenses->clear();

        return redirect()->route('expenses.index');
    }

      public function preview()
    {
        $data = session('expenses');

        if (! $data) {
            return redirect()->route('expenses.create')
                ->with('error', 'Your session expired. Please add your expenses again.');
        }

        return view('guest.expense.preview', compact('data'));
    }

    public function download(ExpenseSummaryService $service)
    {
        $data = session('expenses');

        if (! $data) {
            return redirect()->route('expenses.create')
                ->with('error', 'Your session expired. Please add your expenses again.');
        }

        return response()->streamDownload(function () use ($service, $data) {
            $handle = fopen('php://output', 'w');
            $service->writeCsv($handle, $data);
            fclose($handle);
        }, $data['reference'] . '.csv', ['Content-Type' => 'text/csv']);
    }
}
