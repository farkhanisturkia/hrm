<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceMultiSheetExport implements WithMultipleSheets
{
    protected $query;

    public function __construct($query = [])
    {
        $this->query = $query;
    }

    public function sheets(): array
    {
        $sheets = [];
        $isSummary = isset($this->query['summary']) && filter_var($this->query['summary'], FILTER_VALIDATE_BOOLEAN);

        // JIKA TOMBOL "EXPORT SUMMARY" DIKLIK
        if ($isSummary) {
            $sheets[] = new SummaryAttendanceExport($this->query);
            return $sheets;
        }

        // JIKA TOMBOL "EXPORT ALL" DIKLIK
        // Cek apakah ada filter user tertentu
        if (isset($this->query['user_id'])) {
            $user = User::find($this->query['user_id']);
            if ($user) {
                $sheets[] = new AttendanceExport($user, $this->query);
            }
        } else {
            // Jika tidak ada filter user, export semua user (tiap user 1 sheet)
            $users = User::orderBy('name')->get();
            foreach ($users as $user) {
                    $sheets[] = new AttendanceExport($user, $this->query);
                // }
            }
        }

        return $sheets;
    }
}