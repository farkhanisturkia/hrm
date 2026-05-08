<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping; 
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithTitle, WithMapping
{
    protected $user;
    protected $query;

    public function __construct($user, $query = [])
    {
        $this->user = $user;
        $this->query = $query;
    }

    public function title(): string
    {
        return $this->user->name ?? 'Unknown User';
    }

    public function collection()
    {
        $attendanceQuery = Attendance::where('user_id', $this->user->id)
            ->orderBy('check_in_time', 'asc');

        if (!empty($this->query['from']) && !empty($this->query['to'])) {
            $from = Carbon::parse($this->query['from'])->startOfDay();
            $to = Carbon::parse($this->query['to'])->endOfDay();
            $attendanceQuery->whereBetween('check_in_time', [$from, $to]);
        }

        return $attendanceQuery->get();
    }

    public function map($attendance): array
    {
        $checkIn = Carbon::parse($attendance->check_in_time);
        $checkOut = $attendance->check_out_time ? Carbon::parse($attendance->check_out_time) : null;
        
        $duration = '-';
        if ($checkOut) {
            $diff = $checkIn->diff($checkOut);
            $duration = $diff->format('%H:%I:%S'); // Format Jam:Menit:Detik
        }

        return [
            $checkIn->format('d-m-Y'), // Tanggal
            $checkIn->format('H:i:s'), // Jam Masuk
            $checkOut ? $checkOut->format('H:i:s') : 'Not Checked Out', // Jam Pulang
            $duration, // Durasi Kerja
        ];
    }

    public function headings(): array
    {
        return ['Date', 'Check In', 'Check Out', 'Work Duration'];
    }
}