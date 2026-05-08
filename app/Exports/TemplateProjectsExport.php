<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TemplateProjectsExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return collect([
            [
                'projectOwner_id' => 1,
                'name' => 'example_project_name',
            ]
        ]);
    }

    public function headings(): array
    {
        return ['projectOwner_id', 'name'];
    }

    public function title(): string
    {
        return 'projects';
    }
}
