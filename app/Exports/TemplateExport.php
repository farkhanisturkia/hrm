<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'users' => new TemplateUsersExport,
            'projectOwners' => new TemplateProjectOwnersExport,
            'projects' => new TemplateProjectsExport,
            'tasks' => new TemplateTasksExport,
        ];
    }
}
