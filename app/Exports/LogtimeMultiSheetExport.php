<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LogtimeMultiSheetExport implements WithMultipleSheets
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function sheets(): array
    {
        $isSummary = isset($this->query['summary']) && filter_var($this->query['summary'], FILTER_VALIDATE_BOOLEAN);
        $exportClass = $isSummary ? SummaryLogtimeExport::class : LogtimeExport::class;

        $usersQuery = User::query();

        if (!empty($this->query['user_id'])) {
            $usersQuery->where('id', $this->query['user_id']);
        }

        $users = $usersQuery->get();
        $sheets = [];

        foreach ($users as $user) {
            $sheets[] = new $exportClass($user, $this->query);
        }

        return $sheets;
    }
}
