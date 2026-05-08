<?php

namespace App\Exports;

use App\Models\Logtime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class LogtimeExport implements FromCollection, WithHeadings, WithTitle
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
        $logtimesQuery = Logtime::with(['user', 'task'])
            ->where('user_id', $this->user->id)
            ->orderBy('date', 'desc');

        if (!empty($this->query['from']) && !empty($this->query['to'])) {
            $from = Carbon::parse($this->query['from'])->startOfDay();
            $to = Carbon::parse($this->query['to'])->endOfDay();
            $logtimesQuery->whereBetween('date', [$from, $to]);
        }

        return $logtimesQuery->get()->map(function ($logtime) {
            return [
                'issue'       => $logtime->task?->issue ?? 'N/A',
                'ticket_link' => $logtime->task?->ticket_link ?? 'N/A',
                'date'        => Carbon::parse($logtime->date)->format('d-m-Y'),
                'time_used'   => $logtime->time_used,
            ];
        });
    }

    public function headings(): array
    {
        return ['Issue', 'Ticket', 'Date', 'Hours'];
    }
}