@extends('layouts.app')

@section('title', 'Plotting KKN — Pemilihan Kelompok')

@push('css')
<link rel="stylesheet" href="{{ asset('css/war.css') }}">
@endpush

@section('content')

<section class="section">

    <div class="section-header">
        <h1>Plotting KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ route('home') }}">Dashboard</a>
            </div>
            <div class="breadcrumb-item">Plotting KKN</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ── HERO ────────────────────────────────── --}}
        <div class="war-hero">
            <div class="war-hero-content">

                @if($activeWar)
                    <div class="war-badge" role="status" aria-label="Status sesi plotting">
                        <span class="blink" aria-hidden="true"></span>
                        LIVE — SESI PLOTTING SEDANG BERLANGSUNG
                    </div>
                    <h1>{{ $activeWar->name }}</h1>
                    <p>
                        Gelombang: <strong>{{ $activeWar->gelombang->nama_gelombang ?? '-' }}</strong>
                        &nbsp;·&nbsp;
                        Berakhir: <strong>{{ $activeWar->end_at?->format('d M Y, H:i') }}</strong>
                    </p>
                @else
                    <h1>Plotting KKN</h1>
                    <p>Sistem Pemilihan Kelompok KKN Secara Realtime</p>
                @endif

            </div>
        </div>

        {{-- ── BLOK STATUS ─────────────────────────── --}}
        @if($warStatus === 'no_war')

            <div class="card">
                <div class="card-body state-block">
                    <span class="state-block-icon" aria-hidden="true">
                        <i class="fas fa-calendar-times"></i>
                    </span>
                    <h4 class="font-weight-bold mb-2">Tidak Ada Sesi Plotting yang Sedang Aktif</h4>
                    <p class="text-muted mb-0">
                        Belum ada sesi Plotting yang aktif saat ini. Pantau jadwal yang akan datang di bawah ini.
                    </p>
                </div>
            </div>

        @elseif($warStatus === 'not_registered')

            <div class="alert alert-warning d-flex align-items-center shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle fa-lg mr-3" aria-hidden="true"></i>
                <div>
                    <strong>Kamu belum terdaftar</strong> di gelombang Plotting yang sedang aktif.
                    Pastikan kamu telah mendaftar KKN di gelombang yang sesuai.
                </div>
            </div>

        @elseif($warStatus === 'not_approved')

            <div class="alert alert-warning d-flex align-items-center shadow-sm" role="alert">
                <i class="fas fa-hourglass-half fa-lg mr-3" aria-hidden="true"></i>
                <div>
                    <strong>Pendaftaran KKN-mu belum disetujui.</strong>
                    Status saat ini: <strong>{{ $peserta?->status_pendaftaran }}</strong>.
                    Hubungi administrator jika ada kendala.
                </div>
            </div>

        @elseif($warStatus === 'already_joined')

            <div class="alert alert-success d-flex align-items-center shadow-sm" role="status">
                <i class="fas fa-check-circle fa-lg mr-3" aria-hidden="true"></i>
                <div>
                    Kamu sudah bergabung ke kelompok
                    <strong>{{ $peserta?->kelompokKkn?->nama_kelompok }}</strong>.
                    <a href="{{ route('kelompok.index') }}" class="ml-2 font-weight-bold">
                        Lihat detail &rarr;
                    </a>
                </div>
            </div>

        @elseif($warStatus === 'ready')

            <div class="card">
                <div class="card-body state-block">
                    <span class="state-block-icon" aria-hidden="true">
                        <i class="fas fa-bolt"></i>
                    </span>
                    <h4 class="font-weight-bold mb-2">Sesi Plotting Sedang Berlangsung!</h4>
                    <p class="text-muted mb-4">
                        Kamu eligible untuk ikut pemilihan kelompok. Segera masuk sebelum kuota penuh!
                    </p>
                    <a href="{{ route('war.arena', $activeWar) }}" class="btn btn-war btn-lg" aria-label="Pilih kelompok sekarang">
                        <i class="fas fa-hand-pointer mr-2" aria-hidden="true"></i>
                        Pilih Kelompok Sekarang
                    </a>
                </div>
            </div>

        @endif

        {{-- ── STATISTIK WAR AKTIF ──────────────────── --}}
        @if($activeWar)

            <div class="row mt-4" id="war-stats-row" role="region" aria-label="Statistik sesi plotting">

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-blue">
                        <div class="icon-wrap"><i class="fas fa-users" aria-hidden="true"></i></div>
                        <h2 id="stat-peserta">{{ $warStats['total_peserta'] ?? '–' }}</h2>
                        <p>Peserta Bergabung</p>
                    </div>
                </div>

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-green">
                        <div class="icon-wrap"><i class="fas fa-home" aria-hidden="true"></i></div>
                        <h2 id="stat-kelompok-sisa">{{ $warStats['kelompok_sisa'] ?? '–' }}</h2>
                        <p>Kelompok Tersisa</p>
                    </div>
                </div>

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-red">
                        <div class="icon-wrap"><i class="fas fa-lock" aria-hidden="true"></i></div>
                        <h2 id="stat-kelompok-penuh">{{ $warStats['kelompok_penuh'] ?? '–' }}</h2>
                        <p>Kelompok Penuh</p>
                    </div>
                </div>

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-orange">
                        <div class="icon-wrap"><i class="fas fa-clock" aria-hidden="true"></i></div>
                        <div id="war-countdown" aria-live="polite" aria-label="Waktu tersisa">–</div>
                        <p>Waktu Tersisa</p>
                    </div>
                </div>

            </div>

        @endif

        {{-- ── JADWAL WAR MENDATANG ─────────────────── --}}
        @if($scheduledWars->count())

            <div class="card mt-2">

                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-alt mr-2 text-primary" aria-hidden="true"></i>
                        Jadwal Sesi Plotting Mendatang
                    </h4>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Nama Sesi Plotting</th>
                                    <th scope="col">Gelombang</th>
                                    <th scope="col">Waktu Mulai</th>
                                    <th scope="col">Waktu Selesai</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($scheduledWars as $war)
                                    <tr class="schedule-row">
                                        <td><strong>{{ $war->name }}</strong></td>
                                        <td>{{ $war->gelombang->nama_gelombang ?? '-' }}</td>
                                        <td>{{ $war->start_at?->format('d M Y, H:i') }}</td>
                                        <td>{{ $war->end_at?->format('d M Y, H:i') }}</td>
                                        <td>
                                            <span class="badge badge-warning" style="border-radius:20px;padding:5px 12px;">
                                                <i class="fas fa-clock mr-1" aria-hidden="true"></i> Terjadwal
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        @endif

    </div>

</section>

@endsection

@push('scripts')
@if($activeWar)
<script>
(function () {
    const STATUS_URL  = "{{ route('war.status', $activeWar) }}";
    const END_AT      = new Date("{{ $activeWar->end_at?->toISOString() }}");

    // Hitung Mundur
    function updateCountdown() {
        const diff = END_AT - new Date();
        if (diff <= 0) {
            document.getElementById('war-countdown').textContent = 'SELESAI';
            return;
        }
        const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
        const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
        const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
        document.getElementById('war-countdown').textContent = `${h}:${m}:${s}`;
    }

    setInterval(updateCountdown, 1000);
    updateCountdown();

    // Poll statistik
    function fetchStats() {
        fetch("{{ route('admin.war.monitor.stats', $activeWar) }}")
            .then(r => r.json())
            .then(d => {
                document.getElementById('stat-peserta').textContent        = d.total_peserta ?? '–';
                document.getElementById('stat-kelompok-sisa').textContent  = d.kelompok?.tersisa ?? '–';
                document.getElementById('stat-kelompok-penuh').textContent = d.kelompok?.penuh   ?? '–';

                if (d.war_status === 'closed') {
                    location.reload();
                }
            })
            .catch(() => {});
    }

    fetchStats();
    setInterval(fetchStats, 30000);
})();
</script>
@endif
@endpush
