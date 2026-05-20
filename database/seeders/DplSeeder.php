<?php

namespace Database\Seeders;

use App\Models\DosenPembimbingLapangan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DplSeeder extends Seeder
{
    public function run(): void
    {
        $dpls = [
            ['nama' => 'Kusumawati, M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0005099305'],
            ['nama' => 'Nisa Ariantini, M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0006109302'],
            ['nama' => 'Tuti Azizah, M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '198911112025062003'],
            ['nama' => 'Nurul Fadilah, S.Pd.I., M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0026069301'],
            ['nama' => 'Nurul Azizah, M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '199411252025062009'],
            ['nama' => 'Siti Rahmi, S.Sos.I.,M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0028088304'],
            ['nama' => 'Tri Cahyono, M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0006019005'],
            ['nama' => 'Erna Dwi Nugraini, M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '199503172025062012'],
            ['nama' => 'Desy Irsalina Savitri, S.Pd., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0001119002'],
            ['nama' => 'Dedi Kusnadi, M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0004028902'],
            ['nama' => 'Ady Saputra, M. Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0024118904'],
            ['nama' => 'Mety Toding Bua, M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0002079202'],
            ['nama' => 'Siti Sulistyani Pamuji, M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1123038701'],
            ['nama' => 'Dr. Asih Riyanti, S.Pd., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0007068701'],
            ['nama' => 'Muhammad Thobroni, S.S., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0725087802'],
            ['nama' => 'Ade Armansa, S.Pd., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '199907132025061007'],
            ['nama' => 'Dr. Inung Setyami, S.S.,M.A', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0013078305'],
            ['nama' => 'Dr. Dwi Cahyono Aji, S.S., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1127127801'],
            ['nama' => 'Nurul Hanna Fauziyyah, M.A.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0001119108'],
            ['nama' => 'Muhammad Ilham, S.S., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0010129001'],
            ['nama' => 'Rita Kumala Sari, S.Pd., M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1127018701'],
            ['nama' => 'Siti Fathonah, M. Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1115098801'],
            ['nama' => 'Dr. Erna Wahyuni, S.S., M.A.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1124088001'],
            ['nama' => 'Dr. Ramli, S.S., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0926088701'],
            ['nama' => 'Nursia, S.Pd., M.Si', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1124049302'],
            ['nama' => 'Darius Rupa, S.Pd., M.Si.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1115118701'],
            ['nama' => 'Donna Rhamdan, S.E., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0023068210'],
            ['nama' => 'Nur Pangesti Apriliyana, M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0010069404'],
            ['nama' => 'Rustam .E. Simamora, S.Pd., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0029088804'],
            ['nama' => 'Maharani Izzatin, S.Pd., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1129018603'],
            ['nama' => 'Setia Widia Rahayu, S.Pd., M.Pd.', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1109048702'],
            ['nama' => 'Fatmawati, M.Pd', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '0010048807'],
            ['nama' => 'Ermawaty Maradhy, S.S., M.Si', 'fakultas' => 'Keguruan dan Ilmu Pendidikan', 'nidn' => '1107096901'],
            ['nama' => 'Reza Bintangdari Johan, M.Keb', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '28089307'],
            ['nama' => 'Annisa Eka Permatasari, S.Tr.Keb., M.Keb', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '0031089502'],
            ['nama' => 'Selvia Febrianti, S.S.T.Keb., M.K.M', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '0011029205'],
            ['nama' => 'Darni, S.Kep., Ns., M.Kep', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '9900987316'],
            ['nama' => 'Dewy Haryanti Parman, M.Kep.Ns., Sp.Kep.M.B', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '1117027901'],
            ['nama' => 'Ns. Gusni Fitri, S.Kep., M.Kep', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '0025089305'],
            ['nama' => 'Hendy Lesmana, S.Kep.Ns., M.Kep', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '1113027601'],
            ['nama' => 'Cici Ismuniar, M.Psi.', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '0024129103'],
            ['nama' => 'Nazwa Manurung, S.Psi., M.Psi., Psikolog', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '1316038401'],
            ['nama' => 'Ari Rahmi Hasfaraini, M.Psi', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '0010019307'],
            ['nama' => 'Nurul Hidayah, S.Psi., M.Psi., Psikolog.', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '199201282024062001'],
            ['nama' => 'Alfianur, S.Kep.Ns., M.Kep', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '1123087901'],
            ['nama' => 'Ika Yulianti, S.SiT Bdn, M.K.M', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '0626078601'],
            ['nama' => 'Nurrahmi Umami, S.Tr.Keb. M.Keb', 'fakultas' => 'Ilmu Kesehatan', 'nidn' => '0922099401'],
            ['nama' => 'Dr.Nia Kurniasih Suryana, SP.,MP', 'fakultas' => 'Pertanian', 'nidn' => '1126087402'],
            ['nama' => 'Dr. Dewi Elviana CCW, SP., M.Si.', 'fakultas' => 'Pertanian', 'nidn' => '0007017804'],
            ['nama' => 'Dr. Etty Wahyuni MS, S.Hut., M.P.', 'fakultas' => 'Pertanian', 'nidn' => '1130057401'],
            ['nama' => 'Dr. Sekar Inten Mulyani, S.Pt., M.Si', 'fakultas' => 'Pertanian', 'nidn' => '0013048208'],
            ['nama' => 'Banyuriatiga, S.P.,M.Sc', 'fakultas' => 'Pertanian', 'nidn' => '0010099306'],
            ['nama' => 'Zul Hafandi., S.P.,M.Si', 'fakultas' => 'Pertanian', 'nidn' => '199011192019031012'],
            ['nama' => 'Arwan, S.P., M.Si', 'fakultas' => 'Pertanian', 'nidn' => '199503232025061003'],
            ['nama' => 'Rayhana Jafar, S.P.,M.Agr', 'fakultas' => 'Pertanian', 'nidn' => '0004078307'],
            ['nama' => 'Ulfa Rohmatul Khasanah, S.Pi.,MP', 'fakultas' => 'Pertanian', 'nidn' => '199509222025062009'],
            ['nama' => 'Dr Siti Zahara, SP., MP', 'fakultas' => 'Pertanian', 'nidn' => '1105077202'],
            ['nama' => 'Dr. Nur Indah Mansyur, SP., MP', 'fakultas' => 'Pertanian', 'nidn' => '1115087601'],
            ['nama' => 'Abdul Rahim, S.P., M.Si., Ph.D', 'fakultas' => 'Pertanian', 'nidn' => '1116127801'],
            ['nama' => 'Miska Sanda Lembang, S.Si., M.Si', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0017049201'],
            ['nama' => 'Awaludin, S.Pi., M.Si', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0021089001'],
            ['nama' => 'Dr. Ery Gusman, S.Pi., MP', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '1108088002'],
            ['nama' => 'Ricky Febrinaldy Simanjuntak, S.Pd., M.Si', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0004028801'],
            ['nama' => 'Dr. Gloria Ika Satriani, S.Pi., M.Si.', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0024038602'],
            ['nama' => 'Yushra, S.Kel., M.Si', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '199004062025061002'],
            ['nama' => 'Dr. Asbar Laga, ST., M.Si', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '1105087201'],
            ['nama' => 'Tuty Alawiyah, S.Si., M.Sc.', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0820048502'],
            ['nama' => 'Dr. Muhammad Firdaus, S.Pi., M.Si', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '1123027301'],
            ['nama' => 'Gazali Salim, S.Kel., M.Si.', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '1123018401'],
            ['nama' => 'Dr. Tri Paus Hasiholan Hutapea, S.Si., M.Si.', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0003118902'],
            ['nama' => 'Nurasmi, S.Pd., M.Si.', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0012068703'],
            ['nama' => 'Christine Dyta Nugraeni, S.Si., M.Si', 'fakultas' => 'Perikanan dan Ilmu Kelautan', 'nidn' => '0023099301'],
            ['nama' => 'Abdul Muis Prasetia, S.T., M.T', 'fakultas' => 'Teknik', 'nidn' => '0003018705'],
            ['nama' => 'Dr. Ir. Patria Julianto, S.T., M.T., IPM.', 'fakultas' => 'Teknik', 'nidn' => '1109078101'],
            ['nama' => 'Ir. Fitriani Said, S.T., M.T', 'fakultas' => 'Teknik', 'nidn' => '1122087201'],
            ['nama' => 'Shinta Tri Kismanti, S.Pd., M.Si', 'fakultas' => 'Teknik', 'nidn' => '0028028806'],
            ['nama' => 'Hadi Santoso, S.Pd., M.Si', 'fakultas' => 'Teknik', 'nidn' => '0013078902'],
            ['nama' => 'Marhadi Budi Waluyo, S.T., M.T', 'fakultas' => 'Teknik', 'nidn' => '0018019107'],
            ['nama' => 'Andi Ard Maidhah, S.T., M.T', 'fakultas' => 'Teknik', 'nidn' => '0024129301'],
            ['nama' => 'Sudirman, S.T., M.T.', 'fakultas' => 'Teknik', 'nidn' => '0015128604'],
            ['nama' => 'Muh. Firdan Nurdin, S.T., M.T', 'fakultas' => 'Teknik', 'nidn' => '0009039306'],
            ['nama' => 'Iif Ahmad Syarif, S.T., M.T', 'fakultas' => 'Teknik', 'nidn' => '0003078806'],
            ['nama' => 'Muhammad Kurnia, S.T., M.T.', 'fakultas' => 'Teknik', 'nidn' => '199204032024061001'],
            ['nama' => 'Dr. Witri Yuliawati, S.E.,M.Si', 'fakultas' => 'Ekonomi', 'nidn' => '1103077501'],
            ['nama' => 'Dr. Adhy Satya Pratama, S.E., M.Si.', 'fakultas' => 'Ekonomi', 'nidn' => '198512032024061000'],
            ['nama' => 'Rusdy Setiawan, S.Pd., M.E', 'fakultas' => 'Ekonomi', 'nidn' => '0013079304'],
            ['nama' => 'Dr. Shalahuddin, S.E., M.M', 'fakultas' => 'Ekonomi', 'nidn' => '0003018407'],
            ['nama' => 'Ferawati Usman, S.E., M.M.', 'fakultas' => 'Ekonomi', 'nidn' => '0004028702'],
            ['nama' => 'Rahmi Nur Islami, M.M', 'fakultas' => 'Ekonomi', 'nidn' => '0931129502'],
            ['nama' => 'Nurjannatul Hasanah, S.E., M.M', 'fakultas' => 'Ekonomi', 'nidn' => '1117097704'],
            ['nama' => 'Muhammad Husin Ali, S.H., M.H', 'fakultas' => 'Hukum', 'nidn' => '200007082024061000'],
            ['nama' => 'Dr.Nurasikin, S.H.I., M.H', 'fakultas' => 'Hukum', 'nidn' => '1111107801'],
            ['nama' => 'Inggit Akim, S.H., M.H', 'fakultas' => 'Hukum', 'nidn' => '1125058401'],
            ['nama' => 'Liza Shahnaz, S.IP.,M.HSC.,M.H', 'fakultas' => 'Hukum', 'nidn' => '0023078108'],
            ['nama' => 'Sukmawaty Arisa Gustina, S.H., M.H', 'fakultas' => 'Hukum', 'nidn' => '0029088303'],
            ['nama' => 'Dr. Arif Rohman, S.H.I., LL.M', 'fakultas' => 'Hukum', 'nidn' => '1124098203'],
            ['nama' => 'Ismail, M.H.', 'fakultas' => 'Hukum', 'nidn' => '199506022024061001'],
            ['nama' => 'Avinda Sari Kanthi Rahayu, S.Pd., M.Biomed', 'fakultas' => 'Kedokteran', 'nidn' => '199307302025062004'],
            ['nama' => 'Eka Nurul Hidayah Puspa Seruni, S.KM., M.Kes', 'fakultas' => 'Kedokteran', 'nidn' => '199609242025062013'],
            ['nama' => "Ni'mah Hidayatul Laili, S.S.T.Keb,. M.Biomed", 'fakultas' => 'Kedokteran', 'nidn' => '199609242025062013'],
            ['nama' => 'Dr. dr. Tri Astuti Sugiyatmi, MPH', 'fakultas' => 'Kedokteran', 'nidn' => '0031107204'],
            ['nama' => 'Adipura Atmadja Egok, S.Si., M.Biomed.', 'fakultas' => 'Kedokteran', 'nidn' => '199402092025061004'],
            ['nama' => 'Dr. Ratno Achyani, S.Pi., M.Si.', 'fakultas' => '', 'nidn' => '1129078101'],
            ['nama' => 'Anang Sulistyo, S.P., M.P.', 'fakultas' => '', 'nidn' => '1115078001'],
            ['nama' => 'Dr. Marthen B Salinding, S.H., M.H', 'fakultas' => '', 'nidn' => '1115066801'],
            ['nama' => 'Dr. Eng. Linda Sartika, S.T., M.T.', 'fakultas' => '', 'nidn' => '1115047501'],
            ['nama' => 'Dr. Woro Kusmaryani, S.Pd., M.Pd.', 'fakultas' => '', 'nidn' => '1106028301'],
            ['nama' => 'Dr.Muhammad Amien H, S.Pi., M.Si', 'fakultas' => '', 'nidn' => '1114047402'],
            ['nama' => 'Dr.Irawati HM, S.E., M.AK', 'fakultas' => '', 'nidn' => '0928128701'],
            ['nama' => 'Saat Egra, S.Hut., M.Sc., Ph.D.', 'fakultas' => '', 'nidn' => '0020049201'],
            ['nama' => 'Rusmiati, S.ST., M.Keb', 'fakultas' => '', 'nidn' => '198408242006042006'],
        ];

        foreach ($dpls as $data) {
            $fakultas = null;
            $fakultas_id = null;

            if (! empty($data['fakultas'])) {
                $fakultas = \App\Models\Fakultas::where('nama_fakultas', $data['fakultas'])->first();
                if (! $fakultas) {
                    $this->command?->warn('Fakultas tidak ditemukan: ' . $data['fakultas']);
                    continue;
                }
                $fakultas_id = $fakultas->id;
            }

            $email = $data['nidn'] . '@ubt.ac.id';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['nama'],
                    'password' => Hash::make('kknubt2026'),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole('pembimbing');

            DosenPembimbingLapangan::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nidn' => $data['nidn'],
                    'fakultas_id' => $fakultas_id,
                    'status' => 'aktif',
                ]
            );
        }

        $this->command?->info('DPL Seeder selesai. ' . count($dpls) . ' records.');
    }
}
