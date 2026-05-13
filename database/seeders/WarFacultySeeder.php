<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use App\Models\WarFaculty;
use App\Models\WarSession;
use Illuminate\Database\Seeder;

class WarFacultySeeder extends Seeder
{
    public function run(): void
    {
        $sessions     = WarSession::all();
        $fakultasList = Fakultas::orderBy('id')->get();

        if ($sessions->isEmpty()) {
            $this->command->warn('Tidak ada WarSession.');
            return;
        }

        foreach ($sessions as $session) {
            $existing = WarFaculty::where('war_session_id', $session->id)->exists();

            if ($existing) {
                $this->command->warn("Session [{$session->name}] sudah punya war_faculties, skip.");
                continue;
            }

            $slotDurasiMenit = 120;
            $waktuMulai      = $session->start_at->copy();
            $rows            = [];

            foreach ($fakultasList as $fakultas) {
                $rows[] = [
                    'war_session_id' => $session->id,
                    'fakultas_id'    => $fakultas->id,
                    'quota'          => 0,
                    'taken'          => 0,
                    'start_at'       => $waktuMulai->copy(),
                    'end_at'         => $waktuMulai->copy()->addMinutes($slotDurasiMenit),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];

                $waktuMulai->addMinutes($slotDurasiMenit);
            }

            WarFaculty::insert($rows);

            $this->command->info("Session [{$session->name}]: {$fakultasList->count()} slot jadwal dibuat.");
        }
    }
}
