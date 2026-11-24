<?php

namespace App\Http\Controllers;

use \Spatie\Browsershot\Browsershot;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoicePdfService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Throwable;

class InvoiceController extends Controller
{
     public function create()
    {
        return view('guest.invoice.create', [
            'currencies' => config('currencies'),
            'existing' => session('invoice'),
        ]);
    }

    public function store(StoreInvoiceRequest $request, InvoiceService $service)
    {
        session(['invoice' => $service->build($request->validated())]);

        return redirect()->route('invoice.preview');
    }

    public function preview()
    {
        $invoice = session('invoice');

        if (! $invoice) {
            return redirect()->route('invoice.create')
                ->with('error', 'Your session expired. Please create your invoice again.');
        }

        return view('guest.invoice.preview', compact('invoice'));
    }

    // public function download()
    // {
    //     $invoice = session('invoice');

    //     if (! $invoice) {
    //             return redirect()->route('invoice.create')
    //                 ->with('error', 'Your session expired. Please create your invoice again.');
    //         }

    //     // Ensure tmp directory exists
    //     $tmpDir = storage_path('app/tmp');
    //     if (!is_dir($tmpDir)) {
    //         mkdir($tmpDir, 0775, true);
    //     }

    //     $html      = view('guest.invoice.pdf', compact('invoice'))->render();
    //     $filename  = 'preview-' . $invoice['reference'] . '.pdf';
    //     $outputPath = $tmpDir . '/' . $filename;

    //     Browsershot::html($html)
    //         ->format('A4')
    //         ->margins(10, 10, 10, 10)
    //         ->savePdf($outputPath);

    //     return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
    // }

    public function download(InvoicePdfService $pdfService)
{
    $invoice = session('invoice');

    if (! $invoice) {
        return redirect()->route('invoice.create')
            ->with('error', 'Your session expired. Please create your invoice again.');
    }

    try {
        $pdf = $pdfService->render($invoice);
    } catch (Throwable $e) {
        report($e);

        return redirect()->route('invoice.preview')
            ->with('error', 'PDF generation failed. Check storage/logs/laravel.log and see the README.');
    }

    return response()->streamDownload(
        fn () => print($pdf),
        $invoice['reference'] . '.pdf',
        ['Content-Type' => 'application/pdf']
    );
}
}
