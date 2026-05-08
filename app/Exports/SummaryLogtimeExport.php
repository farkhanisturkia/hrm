<?php

namespace App\Exports;

use App\Models\Logtime;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SummaryLogtimeExport implements FromCollection, WithHeadings, WithTitle
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

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $logtimesQuery = Logtime::query()
            ->join('tasks', 'logtimes.task_id', '=', 'tasks.id')
            ->where('logtimes.user_id', $this->user->id);

        if (!empty($this->query['from']) && !empty($this->query['to'])) {
            $from = Carbon::parse($this->query['from']);
            $to = Carbon::parse($this->query['to']);
            $logtimesQuery->whereBetween('logtimes.date', [$from, $to]);
        }

        return $logtimesQuery
            ->select(
                'tasks.issue as issue',
                'tasks.ticket_link as ticket_link',
                DB::raw('SUM(logtimes.time_used) as total_time_used')
            )
            ->groupBy('tasks.issue', 'tasks.ticket_link')
            ->orderBy('tasks.issue')
            ->get()
            ->map(function ($item) {
                return [
                    'issue' => $item->issue ?? 'N/A',
                    'ticket_link' => $item->ticket_link ?? 'N/A',
                    'total_time_used' => $item->total_time_used,
                ];
            });
    }

    public function headings(): array
    {
        return ['Issue', 'Ticket', 'Hours'];
    }
}
