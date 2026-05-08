<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SkillMultiSheetExport implements WithMultipleSheets
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function sheets(): array
    {
        return [
            'This Year' => new CurrentYearSkillExport($this->id),
            'Previous Years' => new PreviousYearsSkillExport($this->id),
        ];
    }
}
