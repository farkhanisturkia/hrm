<?php

namespace App\Exports;

use App\Models\Skill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class PreviousYearsSkillExport implements FromCollection, WithHeadings, WithTitle
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function title(): string
    {
        return 'Previous Years';
    }

    public function collection()
    {
        $currentYear = Carbon::now()->year;

        $skillQuery = Skill::with('user')
            ->join('users', 'skills.user_id', '=', 'users.id')
            ->whereYear('skills.created_at', '<', $currentYear)
            ->orderBy('users.name', 'asc')
            ->orderBy('skills.created_at', 'desc')
            ->select('skills.*');

        // Filter by user_id if provided
        if (!empty($this->id)) {
            $skillQuery->where('user_id', $this->id);
        }

        return $skillQuery->get()->map(function ($skill) {
            return [
                'user_name' => $skill->user?->name ?? 'N/A',
                'created_at' => Carbon::parse($skill->created_at)->format('d-m-Y'),
                'skill' => $skill->skill,
            ];
        });
    }

    public function headings(): array
    {
        return ['Name', 'Date', 'Skill'];
    }
}
