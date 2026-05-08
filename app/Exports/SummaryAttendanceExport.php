<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class SummaryAttendanceExport implements FromCollection, WithHeadings, WithTitle
{
    protected $query;

    public function __construct($query = [])
    {
        $this->query = $query;
    }

    public function title(): string
    {
        return 'Attendance Summary';
    }

    public function collection()
    {
        // Ambil user yang difilter atau semua user
        $usersQuery = User::orderBy('name');
        if (isset($this->query['user_id'])) {
            $usersQuery->where('id', $this->query['user_id']);
        }
        $users = $usersQuery->get();

        $data = [];

        foreach ($users as $user) {
            $attendanceQuery = Attendance::where('user_id', $user->id);

            if (!empty($this->query['from']) && !empty($this->query['to'])) {
                $from = Carbon::parse($this->query['from'])->startOfDay();
                $to = Carbon::parse($this->query['to'])->endOfDay();
                $attendanceQuery->whereBetween('check_in_time', [$from, $to]);
            }

            $attendances = $attendanceQuery->get();
            
            $totalSeconds = 0;
            $daysPresent = $attendances->count();

            foreach ($attendances as $att) {
                if ($att->check_out_time) {
                    $totalSeconds += Carbon::parse($att->check_in_time)
                        ->diffInSeconds(Carbon::parse($att->check_out_time));
                }
            }

            // Konversi total detik ke Jam:Menit
            $totalHours = floor($totalSeconds / 3600);
            $totalMinutes = floor(($totalSeconds % 3600) / 60);

            $data[] = [
                'name' => $user->name,
                'email' => $user->email,
                'days_present' => $daysPresent,
                'total_hours' => sprintf('%02d:%02d', $totalHours, $totalMinutes) . ' hours',
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return ['Employee Name', 'Email', 'Days Present', 'Total Work Hours'];
    }
}