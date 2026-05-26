@extends('layouts.app')
@section('title', 'Kelompok KKN — ' . $kelompok->nama_kelompok)
@push('css')
<style>
    .group-header-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 20px;
        padding: 32px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .group-header-card::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        background: rgba(255,255,255,.04);
        border-radius: 50%;
    }
    .group-photo-wrap {
        position: relative;
        width: 140px; height: 140px;
        flex-shrink: 0;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid rgba(255,255,255,.3);
        background: rgba(255,255,255,.1);
    }
    .group-photo-wrap img {
        width: 100%; height: 100%;
        object-fit: cover;
    }
    .group-photo-upload {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        background: rgba(0,0,0,.6);
        padding: 4px;
        font-size: 11px;
        text-align: center;
        cursor: pointer;
        color: #fff;
        transition: .2s;
    }
    .group-photo-upload:hover { background: rgba(0,0,0,.8); }
    .group-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        margin: 2px 4px;
    }
    .group-nav {
        display: flex;
        justify-content: center;
        gap: 0;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        overflow: hidden;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .group-nav a {
        padding: 14px 20px;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        color: #6c757d;
        border-bottom: 3px solid transparent;
        transition: .2s;
        text-decoration: none;
        white-space: nowrap;
    }
    .group-nav a:hover, .group-nav a.active {
        color: #6777ef;
        border-bottom-color: #6777ef;
        background: #f8f9ff;
    }
    .group-nav a i { margin-right: 6px; }
    [data-bs-theme="dark"] .group-nav {
        background: #1f2430;
        box-shadow: 0 2px 12px rgba(0,0,0,.2);
    }
    [data-bs-theme="dark"] .group-nav a { color: #aab1c1; }
    [data-bs-theme="dark"] .group-nav a:hover,
    [data-bs-theme="dark"] .group-nav a.active {
        background: rgba(103,119,239,.1);
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Kelompok KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Kelompokku</div>
        </div>
    </div>

    <div class="section-body">

        {{-- GROUP HEADER --}}
        <div class="group-header-card">
            <div class="d-flex align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
                <div class="group-photo-wrap" id="photo-wrap">
                    @if($kelompok->foto_kelompok)
                        <img src="{{ asset('storage/'.$kelompok->foto_kelompok) }}" alt="Foto Kelompok">
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100" style="font-size:40px;">👥</div>
                    @endif
                    @if($isKetua)
                        <label class="group-photo-upload" for="photo-input">
                            <i class="fas fa-camera mr-1"></i> Ubah
                        </label>
                        <form action="{{ route('kelompok.upload-photo') }}" method="POST" enctype="multipart/form-data" id="photo-form" style="display:none;">
                            @csrf
                            <input type="file" name="foto_kelompok" id="photo-input" accept="image/*" onchange="document.getElementById('photo-form').submit()">
                        </form>
                    @endif
                </div>

                <div>
                    <div class="mb-2">
                        <span class="group-badge" style="background:rgba(255,255,255,.15);">
                            {{ $kelompok->desaGelombang->gelombang->nama_gelombang ?? 'KKN' }}
                        </span>
                        <span class="group-badge" style="background:rgba(103,119,239,.3);">
                            {{ $kelompok->kode_kelompok }}
                        </span>
                        <span class="group-badge" style="background:rgba(71,195,99,.3);">
                            Status: {{ $kelompok->status }}
                        </span>
                    </div>
                    <h2 style="font-weight:800;margin-bottom:6px;font-size:1.5rem;">
                        {{ $kelompok->nama_kelompok }}
                    </h2>
                    <div style="opacity:.75;font-size:.85rem;">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ $kelompok->desaGelombang->desa->nama_desa ?? '-' }},
                        {{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }},
                        {{ $kelompok->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}
                    </div>
                    <div style="opacity:.6;font-size:.8rem;margin-top:2px;">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ \Carbon\Carbon::parse($kelompok->desaGelombang->gelombang->tgl_mulai ?? now())->format('d M Y') }}
                        &mdash;
                        {{ \Carbon\Carbon::parse($kelompok->desaGelombang->gelombang->tgl_akhir ?? now())->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- NAVIGATION TABS --}}
        <div class="group-nav">
            <a href="{{ route('kelompok.index') }}" class="{{ Request::is('kelompok') ? 'active' : '' }}">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="{{ route('kelompok.proposal.index') }}" class="{{ Request::is('kelompok/proposal*') ? 'active' : '' }}">
                <i class="fas fa-file-alt"></i> Proposal
            </a>
            <a href="#status">
                <i class="fas fa-tasks"></i> Status
            </a>
            <a href="#peserta">
                <i class="fas fa-users"></i> Peserta & DPL
            </a>
            <a href="#tugas">
                <i class="fas fa-upload"></i> Tugas
            </a>
            <a href="#logbook">
                <i class="fas fa-book"></i> Log Book
            </a>
            <a href="#penilaian">
                <i class="fas fa-star"></i> Penilaian
            </a>
        </div>

        {{-- PLACEHOLDER CONTENT --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <span style="font-size:48px;display:block;margin-bottom:12px;">🚧</span>
                <h5 class="font-weight-bold">Fitur Sedang Dalam Pengembangan</h5>
                <p class="text-muted mb-0">
                    Modul Proposal, Status, Peserta, Tugas, Log Book, dan Penilaian akan segera tersedia.
                </p>
            </div>
        </div>

    </div>
</section>
@endsection
