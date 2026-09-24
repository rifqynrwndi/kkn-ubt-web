<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillRegisteredMahasiswaBirthData extends Command
{
    protected $signature = 'mahasiswa:fill-registered-birth-data';

    protected $description = 'Fill birth_place/birth_date and mark biodata complete for mahasiswa registered in gelombang';

    public function handle(): int
    {
        $defaultBirthPlace = 'Tarakan';
        $defaultBirthDate = '2000-01-01';

        // Find mahasiswa who are registered in any gelombang (have peserta_kkn record)
        $registeredUserIds = DB::table('peserta_kkn')
            ->pluck('mahasiswa_id')
            ->toArray();

        if (empty($registeredUserIds)) {
            $this->info('No mahasiswa found registered in any gelombang.');
            return self::SUCCESS;
        }

        // Update registered mahasiswa: fill birth data + mark complete
        $updated = DB::table('mahasiswa')
            ->whereIn('user_id', $registeredUserIds)
            ->where(function ($query) use ($defaultBirthPlace, $defaultBirthDate) {
                $query->whereNull('birth_place')
                    ->orWhereNull('birth_date')
                    ->orWhere('is_biodata_complete', false);
            })
            ->update([
                'birth_place' => $defaultBirthPlace,
                'birth_date' => $defaultBirthDate,
                'is_biodata_complete' => true,
            ]);

        $this->info("Updated {$updated} registered mahasiswa with birth data and marked biodata complete.");

        // Show summary
        $totalRegistered = count($registeredUserIds);
        $this->info("Total registered in gelombang: {$totalRegistered}");
        $this->info("Default birth_place: {$defaultBirthPlace}");
        $this->info("Default birth_date: {$defaultBirthDate}");

        return self::SUCCESS;
    }
}
