<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Imports\MultiSheetImport;
use App\Exports\TemplateExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Validators\ValidationException;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('Import/Index');   
    }

    public function store(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new MultiSheetImport, $request->file('file'));
    
            Auth::user()->logs()->create([
                'target' => 'template',
                'description' => "[IMPORT] template",
            ]);
    
            return back()->with('success', "Import berhasil!");

        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorList = [];
            foreach ($failures as $failure) {
                $row = $failure->row() - 1;
                $attribute = $failure->attribute();
                $errorMessages = $failure->errors();

                foreach ($errorMessages as $message) {
                    $errorList[] = "Baris {$row}, kolom '{$attribute}': {$message}";
                }
            }

            return back()->with([
                'error' => 'Import gagal! Terdapat ' . count($errorList) . ' kesalahan validasi.',
                'import_errors' => $errorList
            ]);

        } catch (\Throwable $th) {
            $errorMessage = 'Import gagal: ' . $th->getMessage() . ' (File: ' . basename($th->getFile()) . ' Baris: ' . $th->getLine() . ')';
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }

    }

    public function template()
    {
        Auth::user()->logs()->create([
            'target' => 'template',
            'description' => "[DOWNLOAD] template",
        ]);

        return Excel::download(new TemplateExport, 'template.xlsx'); 
    }
}