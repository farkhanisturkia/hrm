<?php

namespace App\Models;

use App\Models\Log;
use App\Models\Logtime;
use App\Models\Skill;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'is_wfa_allowed',
        'isActive',
        'password',
        'avatar',
        'face_embedding',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'face_embedding'    => 'array',
            'is_wfa_allowed'    => 'boolean',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function logtimes(): HasMany
    {
        return $this->hasMany(Logtime::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'id', 'id')
            ->whereRaw('(
                JSON_CONTAINS(programmer, CAST(users.id AS JSON)) OR 
                JSON_CONTAINS(designer, CAST(users.id AS JSON)) OR 
                JSON_CONTAINS(communicator, CAST(users.id AS JSON))
            )');
    }
}