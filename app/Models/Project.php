<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectOwner;
use App\Models\Task;

class Project extends Model
{
    protected $fillable = [
        'name',
        'project_owner_id',
        'creator',
        'updater',
        'isDeleted'
    ];

    public function projectOwner()
    {
        return $this->belongsTo(ProjectOwner::class, 'project_owner_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
