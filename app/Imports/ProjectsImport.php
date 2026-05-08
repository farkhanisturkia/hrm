<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\ProjectOwner;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Facades\Auth;

class ProjectsImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows
{
    public function chunkSize(): int
    {
        return 1000;
    }
    
    public function rules(): array
    {
        return [
            'projectowner_id' => 'required', 
            'name' => 'required|string|max:255',
        ];
    }

    public function model(array $row)
    {
        $searchValue = $row['projectowner_id'];
        
        if (is_numeric($searchValue)) {
            $owner = ProjectOwner::find($searchValue);
        } else {
            $owner = ProjectOwner::where('name', $searchValue)->first();
        }

        if (!$owner) {
            throw new \Exception("Project Owner Not Found: " . $searchValue);
        }

        return new Project([
            'project_owner_id' => $owner->id, 
            'name' => $row['name'],
            'creator' => Auth::id(),
            'updater' => Auth::id(),
        ]);
    }
}