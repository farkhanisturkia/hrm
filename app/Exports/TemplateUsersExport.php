<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class TemplateUsersExport implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $roles = ['other', 'pm', 'pg', 'co', 'ds'];

    public function collection()
    {
        return collect([
            [
                'name'     => 'someone',
                'email'    => 'someone@example.com',
                'role'     => 'pg',
                'password' => 'password',
            ]
        ]);
    }

    public function headings(): array
    {
        return ['name', 'email', 'role', 'password'];
    }

    public function title(): string
    {
        return 'users';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->collection()->count() + 1;

                $validation = $sheet->getDataValidation("C2");
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"' . implode(',', $this->roles) . '"');
            },
        ];
    }
}