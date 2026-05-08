<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Log extends Model
{
    protected $fillable = [
        'target',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
