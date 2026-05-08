<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Skill extends Model
{
    protected $fillable = [
        'skill',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
