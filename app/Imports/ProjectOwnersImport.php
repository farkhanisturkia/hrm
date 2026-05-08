<?php

namespace App\Imports;

use App\Models\ProjectOwner;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Auth;

class ProjectOwnersImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows
{
    public function chunkSize(): int
    {
        return 1000;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function model(array $row)
    {
        return new ProjectOwner([
            'name' => $row['name'],
            'creator' => Auth::id(),
            'updater' => Auth::id(),
        ]);
    }
}