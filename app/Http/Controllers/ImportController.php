<?php

namespace App\Http\Controllers;

use App\Exports\TemplateExport;
use App\Imports\MultiSheetImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Import/Index');   
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $user = Auth::user();

        try {
            Excel::import(new MultiSheetImport, $request->file('file'));

            $user->logs()->create([
                'target' => 'template',
                'description' => "[IMPORT] template",
            ]);

            return back()->with('success', "Import successful!");

        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorList = [];
            foreach ($failures as $failure) {
                $row = $failure->row() - 1;
                $attribute = $failure->attribute();
                $errorMessages = $failure->errors();

                foreach ($errorMessages as $message) {
                    $errorList[] = 'Row ' . $row . ', column ' . $attribute . ': ' . $message;
                }
            }

            return back()->with([
                'error' => 'Import failed! There were ' . count($errorList) . ' validation errors.',
                'import_errors' => $errorList
            ]);

        } catch (\Throwable $th) {
            $errorMessage = 'Import failed: ' . $th->getMessage() . ' (File: ' . basename($th->getFile()) . ' Line: ' . $th->getLine() . ')';
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    public function template(): BinaryFileResponse
    {
        $user = Auth::user();

        $user->logs()->create([
            'target' => 'template',
            'description' => "[DOWNLOAD] template",
        ]);

        return Excel::download(new TemplateExport, 'template.xlsx'); 
    }
}