<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DtefRegisterImport;
use App\Imports\DtefResultImport;

class DtefImportController extends Controller
{
    /**
     * Register import form (DTEF register).
     */
    public function showForm(): View
    {
        return view('dtef.import');
    }

    /**
     * Handle DTEF register upload.
     */
    public function handleUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        Excel::import(new DtefRegisterImport(), $request->file('file'));

        return redirect()
            ->route('registrations.index')
            ->with('status', 'DTEF register imported successfully.');
    }

    /**
     * Results import form (DTEF results).
     */
    public function showResultForm(): View
    {
        return view('dtef.results_import');
    }

    /**
     * Handle DTEF results upload.
     */
    public function handleResultUpload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        // ⏱️ allow more time / memory for large result files
        ini_set('max_execution_time', '300');   // 5 minutes
        ini_set('memory_limit', '512M');        // adjust if needed
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        Excel::import(new DtefResultImport(), $request->file('file'));

        return redirect()
            ->route('results.index')
            ->with('status', 'DTEF results imported successfully.');
    }
}

