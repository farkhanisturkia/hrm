<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Task;

class Logtime extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'time_used',
        'description',
    ];

    protected $casts = [
        'time_used' => 'float',
        'date'      => 'date:Y-m-d',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
