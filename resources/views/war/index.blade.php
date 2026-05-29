@extends('layouts.app')

@section('title', 'Plotting KKN — Pemilihan Kelompok')

@push('css')
<style>
    /* ── HERO WAR ──────────────────────────────────── */
    .war-hero {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 20px;
        padding: 48px 36px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .war-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 280px; height: 280px;
        background: rgba(255,59,59,.12);
        border-radius: 50%;
    }
    .war-hero::after {
        content: '';
        position: absolute;
        bottom: -80px; left: -40px;
        width: 220px; height: 220px;
        background: rgba(103,119,239,.1);
        border-radius: 50%;
    }
    .war-hero-content { position: relative; z-index: 1; }

    .war-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,59,59,.2);
        border: 1px solid rgba(255,59,59,.5);
        color: #ff6b6b;
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 16px;
    }
    .war-badge .blink {
        width: 8px; height: 8px;
        background: #ff6b6b;
        border-radius: 50%;
        animation: blink 1s infinite;
    }
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0; }
    }

    .war-hero h1 {
        font-size: 2.4rem;
        font-weight: 800;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    .war-hero p { font-size: 1rem; opacity: .75; margin-bottom: 0; }

    /* ── KARTU STATUS ──────────────────────────────── */
    .status-card {
        border: none;
        border-radius: 16px;
        padding: 28px 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: transform .2s, box-shadow .2s;
    }
    .status-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(0,0,0,.1);
    }
    .status-card .icon-wrap {
        width: 64px; height: 64px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px;
        margin: 0 auto 16px;
    }
    .status-card h2 { font-size: 2rem; font-weight: 800; margin-bottom: 4px; }
    .status-card p  { font-size: 13px; margin-bottom: 0; opacity: .75; }

    .sc-blue   { background: linear-gradient(135deg, #e8ecff, #dde3ff); color: #4f5ece; }
    .sc-green  { background: linear-gradient(135deg, #e8fff0, #d3f9df); color: #2f9e44; }
    .sc-red    { background: linear-gradient(135deg, #fff0f0, #ffd8d8); color: #c92a2a; }
    .sc-orange { background: linear-gradient(135deg, #fff8e1, #ffe0a0); color: #d08800; }

    .sc-blue   .icon-wrap { background: rgba(79,94,206,.15); }
    .sc-green  .icon-wrap { background: rgba(47,158,68,.15); }
    .sc-red    .icon-wrap { background: rgba(201,42,42,.15); }
    .sc-orange .icon-wrap { background: rgba(208,136,0,.15); }

    /* ── BLOK STATUS WAR ───────────────────────────── */
    .state-block {
        border-radius: 16px;
        padding: 40px;
        text-align: center;
    }

    .btn-war {
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 14px 40px;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: .5px;
        transition: all .25s;
        box-shadow: 0 6px 20px rgba(255,65,108,.35);
    }
    .btn-war:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(255,65,108,.45);
        color: #fff;
    }
    .btn-war:active { transform: translateY(0); }

    .schedule-row td { vertical-align: middle !important; }

    #war-countdown {
        font-size: 2rem;
        font-weight: 800;
        color: #ff416c;
        letter-spacing: 2px;
        font-family: 'Courier New', monospace;
    }
</style>
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
                    <div class="war-badge">
                        <span class="blink"></span>
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
                    <span style="font-size:56px;display:block;margin-bottom:16px;">📅</span>
                    <h4 class="font-weight-bold mb-2">Tidak Ada Sesi Plotting yang Sedang Aktif</h4>
                    <p class="text-muted mb-0">
                        Belum ada sesi Plotting yang aktif saat ini. Pantau jadwal yang akan datang di bawah ini.
                    </p>
                </div>
            </div>

        @elseif($warStatus === 'not_registered')

            <div class="alert alert-warning d-flex align-items-center shadow-sm">
                <i class="fas fa-exclamation-triangle fa-lg mr-3"></i>
                <div>
                    <strong>Kamu belum terdaftar</strong> di gelombang Plotting yang sedang aktif.
                    Pastikan kamu telah mendaftar KKN di gelombang yang sesuai.
                </div>
            </div>

        @elseif($warStatus === 'not_approved')

            <div class="alert alert-warning d-flex align-items-center shadow-sm">
                <i class="fas fa-hourglass-half fa-lg mr-3"></i>
                <div>
                    <strong>Pendaftaran KKN-mu belum disetujui.</strong>
                    Status saat ini: <strong>{{ $peserta?->status_pendaftaran }}</strong>.
                    Hubungi administrator jika ada kendala.
                </div>
            </div>

        @elseif($warStatus === 'already_joined')

            <div class="alert alert-success d-flex align-items-center shadow-sm">
                <i class="fas fa-check-circle fa-lg mr-3"></i>
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
                    <span style="font-size:56px;display:block;margin-bottom:16px;">⚡</span>
                    <h4 class="font-weight-bold mb-2">Sesi Plotting Sedang Berlangsung!</h4>
                    <p class="text-muted mb-4">
                        Kamu eligible untuk ikut pemilihan kelompok. Segera masuk sebelum kuota penuh!
                    </p>
                    <a href="{{ route('war.arena', $activeWar) }}" class="btn btn-war btn-lg">
                        <i class="fas fa-hand-pointer mr-2"></i>
                        Pilih Kelompok Sekarang
                    </a>
                </div>
            </div>

        @endif

        {{-- ── STATISTIK WAR AKTIF ──────────────────── --}}
        @if($activeWar)

            <div class="row mt-4" id="war-stats-row">

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-blue">
                        <div class="icon-wrap"><i class="fas fa-users"></i></div>
                        <h2 id="stat-peserta">{{ $warStats['total_peserta'] ?? '–' }}</h2>
                        <p>Peserta Bergabung</p>
                    </div>
                </div>

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-green">
                        <div class="icon-wrap"><i class="fas fa-home"></i></div>
                        <h2 id="stat-kelompok-sisa">{{ $warStats['kelompok_sisa'] ?? '–' }}</h2>
                        <p>Kelompok Tersisa</p>
                    </div>
                </div>

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-red">
                        <div class="icon-wrap"><i class="fas fa-lock"></i></div>
                        <h2 id="stat-kelompok-penuh">{{ $warStats['kelompok_penuh'] ?? '–' }}</h2>
                        <p>Kelompok Penuh</p>
                    </div>
                </div>

                <div class="col-6 col-md-3 mb-3">
                    <div class="status-card sc-orange">
                        <div class="icon-wrap"><i class="fas fa-clock"></i></div>
                        <div id="war-countdown">–</div>
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
                        <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                        Jadwal Sesi Plotting Mendatang
                    </h4>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Sesi Plotting</th>
                                    <th>Gelombang</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Status</th>
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
                                                <i class="fas fa-clock mr-1"></i> Terjadwal
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
