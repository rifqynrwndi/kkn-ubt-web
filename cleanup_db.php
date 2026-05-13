<?php
$bots = App\Models\User::where('email', 'like', 'bot%@test.com')->get();
foreach ($bots as $bot) {
    if ($bot->mahasiswa) {
        $peserta = $bot->mahasiswa->pesertaKkn;
        foreach($peserta as $p) {
            App\Models\WarParticipant::where('peserta_kkn_id', $p->id)->delete();
            $p->delete();
        }
        $bot->mahasiswa->delete();
    }
    $bot->delete();
}

$kelompok = App\Models\KelompokKkn::find(48);
if ($kelompok) {
    $kelompok->status = 'dibuka';
    $kelompok->save();
}

$session = App\Models\WarSession::find(2);
if ($session) {
    $session->faculties()->update(['filled' => 1]); // Reset filled to 1 (Rifqy)
}

echo "Bots deleted and DB cleaned.\n";
