<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new UsersImport(),
            1 => new ProjectOwnersImport(),
            2 => new ProjectsImport(),
            3 => new TasksImport(),
        ];
    }
}