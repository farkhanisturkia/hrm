<?php

namespace App\Imports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TasksImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows
{
    public function chunkSize(): int
    {
        return 1000;
    }
    
    public function rules(): array
    {
        return [
            'project_id' => 'required|numeric',
            'issue' => 'required|string',
            'type' => 'required|in:low,normal,high',
            'ticket_link' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'required|in:active,non-active',
            'start_date' => 'required|string',
            'due_date' => 'nullable|string',
        ];
    }

    public function model(array $row)
    {
        $type = $this->mapType($row['type']);
        $is_active = $this->mapIsActive($row['is_active']);

        return new Task([
            'project_id' => $row['project_id'],
            'issue' => $row['issue'],
            'type' => $type,
            'ticket_link' => $row['ticket_link'],
            'description' => $row['description'],
            'isActive' => $is_active,
            'start_date' => Carbon::parse($row['start_date']),
            'due_date' => $row['due_date'] ? Carbon::parse($row['due_date']) : null,
            'creator' => Auth::id(),
            'updater' => Auth::id(),
        ]);
    }

    private function mapType($value)
    {
        $map = [
            'low' => 'low',
            'normal' => 'normal',
            'high' => 'high',
        ];

        return $map[$value] ?? 'normal';
    }

    private function mapIsActive($value)
    {
        $map = [
            'active' => true,
            'non-active' => false,
        ];

        return $map[$value] ?? true;
    }
}
