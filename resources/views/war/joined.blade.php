@extends('layouts.app')

@section('title', 'Berhasil Bergabung — Plotting KKN')

@push('css')
<style>
.joined-hero {
    background: linear-gradient(135deg, #1e3a2f 0%, #1a4a35 60%, #0d3d27 100%);
    border-radius: 20px;
    padding: 40px 32px;
    color: #fff;
    margin-bottom: 24px;
    text-align: center;
}
.joined-hero .check-icon {
    width: 80px; height: 80px;
    background: rgba(71,195,99,.2);
    border: 3px solid rgba(71,195,99,.5);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 34px;
    margin: 0 auto 20px;
    animation: popIn .5s cubic-bezier(.175,.885,.32,1.275);
}
@keyframes popIn {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}
.joined-hero h1 { font-size: 2rem; font-weight: 800; margin-bottom: 6px; }
.joined-hero p  { font-size: .95rem; opacity: .75; margin: 0; }

.info-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.06);
    overflow: hidden;
}
.info-card .card-header {
    background: transparent;
    border-bottom: 1px solid #f1f1f1;
    padding: 18px 24px;
    font-weight: 700;
    font-size: 15px;
}
.info-card .card-body { padding: 24px; }

.detail-row { padding: 10px 0; border-bottom: 1px solid #f5f5f5; }
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: 12px; color: #adb5bd; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px; }
.detail-value { font-size: 15px; font-weight: 600; color: #1a1a2e; }

/* ── LIST ANGGOTA ──────────────────────────────── */
.anggota-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;
}
.anggota-item:last-child { border-bottom: none; }
.anggota-avatar {
    width: 38px; height: 38px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 14px; flex-shrink: 0;
}
.anggota-avatar.L { background: #e8ecff; color: #4f5ece; }
.anggota-avatar.P { background: #fce4ec; color: #c2185b; }
.anggota-info .name  { font-size: 14px; font-weight: 600; }
.anggota-info .prodi { font-size: 12px; color: #6c757d; }

.badge-faculty {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: #e8ecff;
    color: #4f5ece;
}
</style>
@endpush

@section('content')

<section class="section">

    <div class="section-header">
        <h1>Status Kelompok KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('pendaftaran-kkn.index') }}">Pendaftaran KKN</a></div>
            <div class="breadcrumb-item active">Detail Kelompok</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ── HERO ─────────────────────────────────── --}}
        <div class="joined-hero">
            <div class="check-icon">✓</div>
            @if($session && $participant)
                <h1>Selamat! Kamu Berhasil Bergabung</h1>
                <p>
                    Kamu telah bergabung ke
                    <strong>{{ $peserta->kelompokKkn->nama_kelompok }}</strong>
                    &nbsp;&middot;&nbsp; {{ $participant->joined_at?->diffForHumans() }}
                </p>
            @else
                <h1>Detail Kelompok KKN</h1>
                <p>
                    <strong>{{ $peserta->kelompokKkn->nama_kelompok }}</strong>
                </p>
            @endif
        </div>

        <div class="row">

            {{-- ── DETAIL KELOMPOK ──────────────────── --}}
            <div class="col-md-5 mb-4">
                <div class="card info-card">
                    <div class="card-header">
                        <i class="fas fa-info-circle mr-2 text-primary"></i> Detail Kelompok
                    </div>
                    <div class="card-body">

                        @php $k = $peserta->kelompokKkn; @endphp

                        <div class="detail-row">
                            <div class="detail-label">Nama Kelompok</div>
                            <div class="detail-value">{{ $k->nama_kelompok }}</div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Desa Lokasi KKN</div>
                            <div class="detail-value">
                                {{ $k->desaGelombang?->desa?->nama_desa ?? '-' }}
                                <small class="text-muted d-block">
                                    {{ $k->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '' }}
                                </small>
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Dosen Pembimbing Lapangan</div>
                            <div class="detail-value">
                                {{ $k->dosenPembimbingLapangan?->user?->name ?? 'Belum Ditentukan' }}
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Jumlah Anggota</div>
                            <div class="detail-value">
                                {{ $k->pesertaKkn->count() }} / {{ $k->kuota }} orang
                            </div>
                        </div>

                        <div class="detail-row">
                            <div class="detail-label">Status Kelompok</div>
                            <div class="detail-value">
                                @if($k->is_full)
                                    <span class="badge badge-danger" style="border-radius:20px;padding:6px 14px;">
                                        <i class="fas fa-lock mr-1"></i> Penuh
                                    </span>
                                @else
                                    <span class="badge badge-success" style="border-radius:20px;padding:6px 14px;">
                                        <i class="fas fa-door-open mr-1"></i> Masih Tersedia
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($participant)
                        <div class="detail-row">
                            <div class="detail-label">Waktu Bergabung</div>
                            <div class="detail-value">
                                {{ $participant->joined_at?->format('d M Y, H:i:s') }}
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- ── DAFTAR ANGGOTA ───────────────────── --}}
            <div class="col-md-7 mb-4">
                <div class="card info-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-users mr-2 text-primary"></i> Anggota Kelompok</span>
                        <span class="text-muted" style="font-size:13px;">
                            {{ $k->pesertaKkn->count() }} orang
                        </span>
                    </div>
                    <div class="card-body" style="max-height: 420px; overflow-y: auto;">

                        @forelse($k->pesertaKkn as $p)
                            @php
                                $initials = collect(explode(' ', $p->mahasiswa?->user?->name ?? '?'))
                                    ->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                                $gender   = $p->mahasiswa?->jenis_kelamin ?? 'L';
                                $isMe     = $p->id === $peserta->id;
                            @endphp
                            <div class="anggota-item">
                                <div class="anggota-avatar {{ $gender }}">{{ $initials }}</div>
                                <div class="anggota-info flex-grow-1">
                                    <div class="name">
                                        {{ $p->mahasiswa?->user?->name ?? '-' }}
                                        @if($isMe)
                                            <span class="badge badge-primary ml-1" style="font-size:10px;border-radius:10px;">Kamu</span>
                                        @endif
                                        @if($p->id === $k->ketua_peserta_id)
                                            <span class="badge badge-warning ml-1" style="font-size:10px;border-radius:10px;">Ketua</span>
                                        @endif
                                    </div>
                                    <div class="prodi">
                                        {{ $p->mahasiswa?->prodi?->nama_prodi ?? '-' }}
                                    </div>
                                </div>
                                <span class="badge-faculty">
                                    {{ $p->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-muted text-center py-4">Belum ada anggota terdaftar</p>
                        @endforelse

                    </div>
                </div>
            </div>

        </div>

        {{-- ── TOMBOL KEMBALI ──────────────────────── --}}
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg" style="border-radius:12px;padding:12px 36px;font-weight:700;">
                    <i class="fas fa-home mr-2"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

    </div>

</section>

@endsection
