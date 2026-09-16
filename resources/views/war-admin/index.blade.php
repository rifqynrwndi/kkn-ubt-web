@extends('layouts.app')

@section('title', 'Plotting Kelompok')

@push('css')
<link rel="stylesheet" href="{{ asset('css/war.css') }}">
<style>
    .war-admin-hero {
        background: #fff;
        border-radius: 3px;
        padding: 20px;
        color: #34395e;
        margin-bottom: 28px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
        border: none;
    }
    .war-admin-hero-content { position: relative; z-index: 1; }
    .war-admin-hero h1 { font-size: 1.35rem; font-weight: 700; margin-bottom: 4px; color: #34395e; }
    .war-admin-hero h1 i { margin-right: 12px; color: var(--war-primary); }
    .war-admin-hero p { font-size: .9rem; color: #6c757d; margin-bottom: 0; }

    [data-bs-theme="dark"] .war-admin-hero { background-color: #2a2d36; color: #d6d9df; border-color: #3a3f4b; }
    [data-bs-theme="dark"] .war-admin-hero h1 { color: #f1f3f8; }
    [data-bs-theme="dark"] .war-admin-hero p { color: #aab1c1; }

    .war-summary-bar {
        display: flex;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .war-summary-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--bs-body-bg, #fff);
        border-radius: 12px;
        padding: 14px 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
        flex: 1;
        min-width: 180px;
    }
    .war-summary-icon {
        width: 42px; height: 42px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .war-summary-icon.blue   { background: rgba(103,119,239,.12); color: var(--war-primary); }
    .war-summary-icon.green  { background: rgba(71,195,99,.12); color: var(--war-success); }
    .war-summary-icon.orange { background: rgba(255,164,38,.12); color: var(--war-warning); }
    .war-summary-icon.red    { background: rgba(252,84,75,.12); color: var(--war-danger); }
    .war-summary-text .ws-value { font-size: 1.3rem; font-weight: 800; line-height: 1; }
    .war-summary-text .ws-label { font-size: 12px; color: var(--war-text-muted); margin-top: 2px; }
</style>
@endpush

@section('content')

<section class="section">

    {{-- ── HERO HEADER ────────────────────────────── --}}
    <div class="war-admin-hero">
        <div class="war-admin-hero-content">
            <h1>Plotting Kelompok KKN</h1>
            <p>Kelola sesi Plotting Kelompok KKN. Buat, pantau, dan kelola semua sesi dari satu tempat.</p>
        </div>
    </div>

    <div class="section-body">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    <i class="fas fa-exclamation-circle mr-2"></i> Terjadi kesalahan pada input. Harap periksa kembali.
                </div>
            </div>
        @endif

        {{-- ── SUMMARY BAR ─────────────────────────── --}}
        @php
            $totalWars = $wars->count();
            $activeWars = $wars->where('status', 'active')->count();
            $completedWars = $wars->where('status', 'closed')->count();
            $totalParticipants = $wars->sum('participants_count');
        @endphp
        <div class="war-summary-bar">
            <div class="war-summary-item">
                <div class="war-summary-icon blue"><i class="fas fa-layer-group"></i></div>
                <div class="war-summary-text">
                    <div class="ws-value">{{ $totalWars }}</div>
                    <div class="ws-label">Total Sesi</div>
                </div>
            </div>
            <div class="war-summary-item">
                <div class="war-summary-icon green"><i class="fas fa-play-circle"></i></div>
                <div class="war-summary-text">
                    <div class="ws-value">{{ $activeWars }}</div>
                    <div class="ws-label">Aktif</div>
                </div>
            </div>
            <div class="war-summary-item">
                <div class="war-summary-icon orange"><i class="fas fa-check-circle"></i></div>
                <div class="war-summary-text">
                    <div class="ws-value">{{ $completedWars }}</div>
                    <div class="ws-label">Selesai</div>
                </div>
            </div>
            <div class="war-summary-item">
                <div class="war-summary-icon red"><i class="fas fa-users"></i></div>
                <div class="war-summary-text">
                    <div class="ws-value">{{ $totalParticipants }}</div>
                    <div class="ws-label">Total Peserta</div>
                </div>
            </div>
        </div>

        {{-- ── ACTION ROW ──────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 font-weight-bold">Daftar Semua Sesi Plotting</h5>
            <a href="{{ route('admin.war.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Buat Sesi Plotting Baru
            </a>
        </div>

        {{-- ── TABLE ───────────────────────────────── --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama Sesi Plotting</th>
                                <th>Gelombang KKN</th>
                                <th>Jadwal Pelaksanaan</th>
                                <th>Status</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wars as $war)
                                <tr>
                                    <td>
                                        <strong>{{ $war->name }}</strong>
                                    </td>
                                    <td>{{ $war->gelombang->nama_gelombang ?? '-' }}</td>
                                    <td>
                                        <div class="text-small">
                                            <i class="fas fa-play-circle text-success mr-1"></i>
                                            <span class="text-muted">Mulai:</span> {{ $war->start_at?->format('d M Y, H:i') }}
                                        </div>
                                        <div class="text-small mt-1">
                                            <i class="fas fa-stop-circle text-danger mr-1"></i>
                                            <span class="text-muted">Selesai:</span> {{ $war->end_at?->format('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($war->status === 'active')
                                            <span class="badge badge-success" style="border-radius:20px;padding:5px 12px;">
                                                <i class="fas fa-circle fa-xs mr-1"></i> Aktif
                                            </span>
                                        @elseif($war->status === 'closed')
                                            <span class="badge badge-secondary" style="border-radius:20px;padding:5px 12px;">
                                                <i class="fas fa-lock fa-xs mr-1"></i> Selesai
                                            </span>
                                        @else
                                            <span class="badge badge-warning" style="border-radius:20px;padding:5px 12px;">
                                                <i class="fas fa-clock fa-xs mr-1"></i> Terjadwal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info" style="border-radius:20px;padding:5px 12px;font-size:13px;">
                                            {{ $war->participants_count }} peserta
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.war.show', $war) }}"
                                           class="btn btn-sm btn-primary"
                                           data-toggle="tooltip" title="Kelola Sesi Plotting">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <a href="{{ route('admin.war.monitor', $war) }}"
                                           class="btn btn-sm btn-info"
                                           data-toggle="tooltip" title="Monitor Plotting Live">
                                            <i class="fas fa-desktop"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Belum ada sesi Plotting yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
@endpush
