<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TemplateProjectOwnersExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([
            ['name' => 'example_projectOwner_name']
        ]);
    }

    public function headings(): array
    {
        return ['name'];
    }

    public function title(): string
    {
        return 'projectOwners';
    }
}