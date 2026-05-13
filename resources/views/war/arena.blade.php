@extends('layouts.app')

@section('title', 'Arena WAR KKN')

@push('style')
<style>
    .arena-header {
        background: linear-gradient(135deg, #6777ef 0%, #3a4bce 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(103,119,239,0.3);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .arena-title {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }
    .countdown-box {
        background: rgba(255,255,255,0.2);
        display: inline-block;
        padding: 10px 25px;
        border-radius: 50px;
        font-size: 20px;
        font-weight: bold;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255,255,255,0.4);
    }
    .kelompok-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .kelompok-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .kelompok-header {
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
    }
    .kelompok-title {
        font-size: 18px;
        font-weight: 700;
        color: #34395e;
        margin-bottom: 5px;
    }
    .kelompok-location {
        font-size: 13px;
        color: #6c757d;
    }
    .kelompok-body {
        padding: 20px;
        flex-grow: 1;
    }
    .quota-box {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        margin-bottom: 15px;
    }
    .quota-number {
        font-size: 28px;
        font-weight: 800;
        color: #6777ef;
        line-height: 1;
    }
    .quota-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #98a6ad;
        margin-top: 5px;
    }
    .btn-join {
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 10px;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-body">

        <div class="arena-header">
            <div class="arena-title">{{ $session->name }}</div>
            <p class="mb-3 opacity-75">Fakultas {{ $peserta->mahasiswa->prodi->fakultas->nama_fakultas }}</p>
            <div class="countdown-box" id="countdown">
                Menghitung waktu...
            </div>
        </div>

        <div class="alert alert-info shadow-sm">
            <i class="fas fa-info-circle mr-2"></i> <strong>Tips:</strong> Segera pilih kelompok yang kuotanya masih tersedia. Klik <strong>Ambil Kelompok</strong> untuk bergabung!
        </div>

        <div class="row" id="kelompok-container">
            @forelse($kelompoks as $k)
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card kelompok-card">
                        <div class="kelompok-header">
                            <div class="kelompok-title">{{ $k->nama_kelompok }}</div>
                            <div class="kelompok-location">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                {{ $k->desaGelombang->desa->nama_desa }},
                                {{ $k->desaGelombang->desa->kecamatan->nama_kecamatan }}
                                @if(isset($k->desaGelombang->desa->kecamatan->kabupaten))
                                    , {{ $k->desaGelombang->desa->kecamatan->kabupaten }}
                                @endif
                            </div>
                        </div>
                        <div class="kelompok-body">
                            <div class="quota-box">
                                <div class="quota-number" id="quota-{{ $k->id }}">
                                    {{ $k->pesertaKkn->count() }} / {{ $k->kuota }}
                                </div>
                                <div class="quota-label">Peserta Bergabung</div>
                            </div>

                            <form onsubmit="joinWar(event, {{ $k->id }})" class="mt-auto">
                                <button type="submit" class="btn btn-primary btn-block btn-join" id="btn-{{ $k->id }}">
                                    <i class="fas fa-fist-raised mr-1"></i> Ambil Kelompok
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i><br>
                        Saat ini tidak ada kelompok yang tersedia.
                    </div>
                </div>
            @endforelse
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const endTime = new Date("{{ $warFaculty->end_at ?? $session->end_at }}").getTime();

    // Timer Countdown
    setInterval(function() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            document.getElementById("countdown").innerHTML = "Sesi Berakhir";
            document.querySelectorAll('.btn-join').forEach(btn => btn.disabled = true);
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown").innerHTML = 
            (hours > 0 ? hours + "j " : "") + minutes + "m " + seconds + "s tersisa";
    }, 1000);

    // Join Request
    function joinWar(e, kelompokId) {
        e.preventDefault();
        const btn = document.getElementById('btn-' + kelompokId);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

        fetch(`/war/{{ $session->id }}/join/${kelompokId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(res => {
            if (res.status === 200 && res.body.success) {
                Swal.fire('Berhasil!', 'Kamu berhasil bergabung ke kelompok.', 'success')
                .then(() => {
                    window.location.href = "{{ route('war.joined', $session->id) }}";
                });
            } else {
                Swal.fire('Gagal!', res.body.message || 'Terjadi kesalahan.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-fist-raised mr-1"></i> Ambil Kelompok';
            }
        })
        .catch(err => {
            Swal.fire('Error!', 'Koneksi terputus. Coba lagi.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-fist-raised mr-1"></i> Ambil Kelompok';
        });
    }

    // Auto-refresh (Opsional, untuk melihat kuota real-time jika tidak pakai soket)
    setInterval(() => {
        // Bisa tambahkan polling kuota jika dibutuhkan
    }, 5000);
</script>
@endpush
