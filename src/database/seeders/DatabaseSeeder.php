<?php

namespace Database\Seeders;

use App\Models\Medewerker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@jongerenkaart.be',
        ], [
            'name' => 'Admin',
            'password' => Hash::make('Wachtwoord'),
            'role' => User::ROLE_ADMIN,
            'organisatie_ids' => [],
            'is_active' => true,
            'approved_at' => now(),
        ]);

        foreach (Medewerker::defaults() as $medewerker) {
            Medewerker::query()->updateOrCreate([
                'email' => $medewerker['email'],
                'regio' => $medewerker['regio'],
            ], $medewerker);
        }
    }
}
