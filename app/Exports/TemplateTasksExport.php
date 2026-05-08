<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class TemplateTasksExport implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $types = ['low', 'normal', 'high'];
    protected $isActiveOptions = ['active', 'non-active'];

    public function collection()
    {
        return collect([
            [
                'project_id'    => 1,
                'issue'         => 'example issue',
                'type'          => 'normal',
                'ticket_link'   => 'www.example.com',
                'description'   => '',
                'is_active'     => 'active',
                'start_date'    => '01-01-1945',
                'due_date'      => '',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'project_id',
            'issue',
            'type',
            'ticket_link',
            'description',
            'is_active',
            'start_date',
            'due_date'
        ];
    }

    public function title(): string
    {
        return 'tasks';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $typeValidation = $sheet->getDataValidation("C2");
                $typeValidation->setType(DataValidation::TYPE_LIST);
                $typeValidation->setShowDropDown(true);
                $typeValidation->setFormula1('"' . implode(',', $this->types) . '"');

                $activeValidation = $sheet->getDataValidation("F2");
                $activeValidation->setType(DataValidation::TYPE_LIST);
                $activeValidation->setShowDropDown(true);
                $activeValidation->setFormula1('"' . implode(',', $this->isActiveOptions) . '"');
            },
        ];
    }
}