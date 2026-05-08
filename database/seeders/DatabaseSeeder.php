<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $farkhan = User::factory()->create([
            'name' => 'Farkhan',
            'email' => 'farkhan@kyodo-i.com',
            'role' => 'pg',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);

        $leo = User::factory()->create([
            'name' => 'Leo',
            'email' => 'leo@kyodo-i.com',
            'role' => 'pm',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);

        $wawan = User::factory()->create([
            'name' => 'Wawan',
            'email' => 'wawan@kyodo-i.com',
            'role' => 'pg',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);

        $trisno = User::factory()->create([
            'name' => 'Trisno',
            'email' => 'trisno@kyodo-i.com',
            'role' => 'pg',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);

        $tasya = User::factory()->create([
            'name' => 'Tasya',
            'email' => 'tasya@kyodo-i.com',
            'role' => 'co',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);

        $nora = User::factory()->create([
            'name' => 'Nora',
            'email' => 'nora@kyodo-i.com',
            'role' => 'other',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);

        $aries = User::factory()->create([
            'name' => 'Aries',
            'email' => 'aries@kyodo-i.com',
            'role' => 'ds',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);

        $hr = User::factory()->create([
            'name' => 'HR',
            'email' => 'hr@kyodo-i.com',
            'role' => 'other',
            'avatar' => '/avatars/1.png',
            'password' => Hash::make('user@kndi')
        ]);
    }
}