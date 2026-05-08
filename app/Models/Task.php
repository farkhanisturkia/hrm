<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProjectOwner;
use App\Models\Project;
use App\Models\PullRequest;
use App\Models\Logtime;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'issue',
        'pl',
        'communicator',
        'programmer',
        'designer',
        'reviewer',
        'ticket_link',
        'related_links',
        'description',
        'start_date',
        'due_date',
        'end_date',
        'time_used',
        'isActive',
        'isAssign',
        'creator',
        'updater',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function pullRequests()
    {
        return $this->hasMany(PullRequest::class);
    }

    public function logtimes()
    {
        return $this->hasMany(Logtime::class);
    }

    public function reviewers()
    {
        return $this->hasMany(\App\Models\TaskReviewer::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'communicator' => 'array',
            'programmer' => 'array',
            'designer' => 'array',
            'reviewer' => 'array',
            'related_links' => 'array',
        ];
    }
}
