<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PullRequest;
use App\Models\User; // Tambahkan ini

class Reply extends Model
{
    protected $fillable = [
        'from',
        'pr_links',
        'comment'
    ];

    public function pullRequest()
    {
        return $this->belongsTo(PullRequest::class);
    }

    // --- TAMBAHKAN INI ---
    public function user()
    {
        // Kita kasih tahu Laravel: "Relasi user ini pakai kolom 'from', bukan 'user_id'"
        return $this->belongsTo(User::class, 'from');
    }

    protected function casts(): array
    {
        return [
            'pr_links' => 'array',
        ];
    }
}