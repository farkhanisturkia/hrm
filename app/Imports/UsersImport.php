<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, WithChunkReading, SkipsEmptyRows
{
    public function chunkSize(): int
    {
        return 1000;
    }
    
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:'.User::class,
            'role' => ['required', 'in:other,pm,pg,co,ds'],
            'password' => ['required', Rules\Password::defaults()],
        ];
    }

    public function model(array $row)
    {
        $role = $this->mapRole($row['role']);

        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'role' => $role,
            'password' => Hash::make($row['password'] ?? 'password123'),
        ]);
    }

    private function mapRole($value)
    {
        $value = strtolower(trim($value));
        
        $map = [
            'other' => 'other',
            'pm' => 'pm',
            'pg' => 'pg',
            'co' => 'co',
            'ds' => 'ds',
        ];

        return $map[$value] ?? 'pg';
    }
}
