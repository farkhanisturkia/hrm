<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Project;
use App\Models\Task;

class ProjectOwner extends Model
{
    protected $fillable = [
        'name',
        'creator',
        'updater',
        'isDeleted'
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
