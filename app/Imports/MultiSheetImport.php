<?php

namespace App\Imports;

use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterImport;

class MultiSheetImport implements WithMultipleSheets, WithEvents
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

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                Cache::increment('tasks_cache_version');
                Cache::increment('projects_cache_version');
                Cache::increment('project_owner_cache_version');
                Cache::increment('logtimes_cache_version');
            },
        ];
    }
}