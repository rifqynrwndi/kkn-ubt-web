<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            [
                'email' => 'admin@kknubt.ac.id',
            ],
            [
                'name' => 'Super Admin',
                'password' => Hash::make(env('SUPERADMIN_PASSWORD') ?: Str::random(20)),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['superadmin']);

        Mahasiswa::where('user_id', $admin->id)->delete();
        PesertaKkn::where('mahasiswa_id', $admin->id)->delete();
    }
}
