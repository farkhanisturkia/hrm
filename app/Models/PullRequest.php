<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Task;
use App\Models\Reply;
use App\Models\User; // Tambahkan ini

class PullRequest extends Model
{
    protected $fillable = [
        'from',
        'pr_links',
        'comment'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    // --- TAMBAHKAN INI ---
    public function user()
    {
        return $this->belongsTo(User::class, 'from');
    }

    protected function casts(): array
    {
        return [
            'pr_links' => 'array',
        ];
    }
}