<?php
namespace App\Services;

use App\Models\KelompokKkn;
use App\Models\KelompokStatusHistory;

class StatusService
{
    public const STAGES = [
        0 => ['nama' => 'Tahap Persiapan', 'color' => 'secondary', 'desc' => 'Peserta mempersiapkan anggota lain jika ada dan persyaratan yang diperlukan (di tab Pengumpulan Tugas) sebelum diajukan ke proses "Menunggu Persetujuan Prodi". Perubahan ke proses selanjutnya hanya dapat dilakukan oleh Mahasiswa.'],
        1 => ['nama' => 'Menunggu Persetujuan Prodi', 'color' => 'warning', 'desc' => 'Operator Program Studi dapat menyetujui setiap peserta pada tab "Peserta & Pembimbing". Setelah seluruh peserta disetujui oleh prodinya masing-masing maka status dapat dilanjutkan oleh mahasiswa. Jika ada peserta yang ditolak maka ketua kelompok dapat menghapus peserta tersebut terlebih dahulu sebelum dilanjutkan. Jika ingin menambahkan peserta lain dapat diubah dahulu prosesnya ke "Revisi".'],
        2 => ['nama' => 'Seleksi', 'color' => 'info', 'desc' => 'Proses seleksi dilakukan oleh Koordinator Program. Hal-hal yang dapat dipertimbangkan adalah Identitas Mahasiswa, Data Akademik, dan Persyaratan yang telah dikumpulkan peserta pada tab "Pengumpulan Tugas".'],
        3 => ['nama' => 'Pembekalan', 'color' => 'primary', 'desc' => 'Pada proses ini Koordinator Program dapat memasukkan Pembimbing Lapangan. Mahasiswa dapat mulai memasukkan Log Book, dan mata kuliah yang akan direkognisikan. Setiap mata kuliah yang dipilih untuk direkognisi perlu disetujui oleh Operator Program Studi (tab Rekognisi). Sedangkan Proses pembekalan itu sendiri dapat direncanakan secara khusus oleh Koordinator Program di luar sistem (opsional). Sistem dapat membantu dalam proses pengumpulan bukti pelaksanaan proses pembekalan pada tab "Pengumpulan Tugas" (diatur terlebih dahulu pada konfigurasi program oleh Koordinator Program) atau melalui pengisian Log Book. Untuk melanjutkan ke proses "Berjalan" dapat dilakukan oleh Dosen Pembimbing Lapangan yang telah dimasukkan atau Koordinator Program.'],
        4 => ['nama' => 'Berjalan', 'color' => 'success', 'desc' => 'Mahasiswa melakukan aktivitas KKN sesuai pembekalan yang telah diberikan. Pada proses ini mahasiswa dapat mengisi Log Book, Bimbingan, Mengumpulkan Tugas (Tugas, Laporan, dll.). Pembimbing Lapangan dapat melakukan monitoring pada pengisian Log Book, merespon pengumpulan tugas, dan melakukan bimbingan. Setelah aktivitas terlaksana maka proses dapat dilanjutkan oleh Dosen Pembimbing Lapangan ke "Penyelesaian Tugas & Penilaian".'],
        5 => ['nama' => 'Penyelesaian Tugas & Penilaian', 'color' => 'warning', 'desc' => 'Jika ada penugasan yang belum selesai maka mahasiswa masih dapat segera mengumpulkannya dan Log Book juga harus segera dilengkapi sesuai batas minimum. Setelah Penugasan dan Log Book terselesaikan (Seluruh respon akhir pada Pengumpulan Tugas harus berstatus "Diterima") maka Penilai dapat melakukan Penilaian. Jika Proses Penilaian telah selesai maka Dosen Pembimbing Lapangan dapat mengakhiri pelaksanaan aktivitas KKN dengan cara melanjutkan proses ke "Rekognisi". Pesan khusus untuk Dosen Pembimbing: jika form penilaian masih kosong, silakan menghubungi Koordinator Program untuk memasukkan komponen penilaian.'],
        6 => ['nama' => 'Rekognisi', 'color' => 'info', 'desc' => 'Operator Prodi, KKN, dan PLP melakukan rekognisi akhir kepada pelaksanaan kegiatan KKN mahasiswa. Nilai rekognisi yang diberikan dapat lebih dari/sama dengan/kurang dari Nilai Kegiatan yang diperoleh oleh mahasiswa. Hal-hal yang dapat dipertimbangkan adalah Identitas Mahasiswa, Data Akademik, Log Book, Bimbingan, Nilai Kegiatan dan Persyaratan yang telah dikumpulkan peserta pada tab "Pengumpulan Tugas". Jika seluruh mata kuliah yang direncanakan telah direkognisi maka Koordinator Program dapat mengubah ke status "Selesai" untuk mengunci Rekognisi.'],
        7 => ['nama' => 'Selesai', 'color' => 'dark', 'desc' => 'Aktivitas KKN telah berakhir dan rekognisi bersifat final (tidak dapat menambah/menghapus/mengubah nilai mata kuliah rekognisi).'],
    ];

    public function changeStatus(KelompokKkn $kelompok, int $newStage, string $keterangan = null, string $role = 'superadmin'): void
    {
        $oldStage = $kelompok->status_tahap;

        if ($newStage === $oldStage) return;

        $kelompok->update(['status_tahap' => $newStage]);

        KelompokStatusHistory::create([
            'kelompok_kkn_id' => $kelompok->id,
            'status_lama' => $oldStage,
            'status_baru' => $newStage,
            'keterangan' => $keterangan,
            'changed_by' => auth()->id(),
            'changed_by_role' => $role,
        ]);
    }

    public function getCurrentStage(KelompokKkn $kelompok): array
    {
        return self::STAGES[$kelompok->status_tahap] ?? self::STAGES[0];
    }

    public function getHistory(KelompokKkn $kelompok)
    {
        return KelompokStatusHistory::where('kelompok_kkn_id', $kelompok->id)
            ->with('changedBy')
            ->latest()
            ->get();
    }
}
