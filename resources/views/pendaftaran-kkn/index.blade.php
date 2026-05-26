@extends('layouts.app')

@section('title', 'Program KKN')

@section('content')

<style>
    .program-card {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,.04);
    }

    .program-header {
        padding: 24px;
        border-bottom: 1px solid rgba(0,0,0,.06);
    }

    [data-bs-theme="dark"] .program-header {
        border-color: rgba(255,255,255,.08);
    }

    .program-title {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .program-subtitle {
        opacity: .7;
        font-size: 14px;
    }

    .top-action {
        gap: 10px;
    }

    .search-box {
        max-width: 320px;
    }

    .custom-table {
        margin-bottom: 0;
    }

    .custom-table thead th {
        border-top: none !important;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .4px;
        opacity: .7;
        white-space: nowrap;
    }

    .custom-table td {
        vertical-align: top !important;
        padding-top: 18px !important;
        padding-bottom: 18px !important;
    }

    .program-name {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .program-location {
        font-size: 14px;
        line-height: 1.6;
    }

    .program-meta {
        font-size: 13px;
        line-height: 1.8;
    }

    .kode-box {
        display: inline-flex;
        align-items: center;
        padding: 8px 14px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        letter-spacing: .6px;
        background: rgba(103,119,239,.12);
        color: #6777ef;
    }

    .status-badge {
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .empty-program {
        padding: 70px 20px;
        text-align: center;
    }

    .empty-program i {
        font-size: 58px;
        margin-bottom: 18px;
        opacity: .3;
    }

    .empty-program h5 {
        font-weight: 700;
        margin-bottom: 10px;
    }

    .empty-program p {
        opacity: .7;
        max-width: 500px;
        margin: auto;
    }

    .table-responsive {
        overflow-x: auto;
    }

    @media (max-width: 768px) {
        .program-header {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .top-action {
            width: 100%;
            margin-top: 16px;
            flex-direction: column;
            align-items: stretch !important;
        }

        .search-box {
            max-width: 100%;
        }
    }
</style>

<section class="section">

    <div class="section-header">
        <h1>Program KKN</h1>
    </div>

    <div class="section-body">

        {{-- ALERT --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        {{-- TIDAK ADA GELOMBANG --}}
        @if(!$gelombang)

            <div class="card program-card">
                <div class="empty-program">
                    <i class="fas fa-calendar-times"></i>

                    <h5>Belum Ada Gelombang Aktif</h5>

                    <p>
                        Saat ini belum tersedia gelombang KKN yang dibuka.
                        Silakan tunggu informasi dari administrator.
                    </p>
                </div>
            </div>

        @else

            {{-- HEADER --}}
            <div class="card program-card mb-4">

                <div class="program-header d-flex justify-content-between align-items-center">

                    <div>
                        <div class="program-title">
                            {{ $gelombang->nama_gelombang }}
                        </div>

                        <div class="program-subtitle">
                            Tahun {{ $gelombang->tahun }}
                            •
                            {{ \Carbon\Carbon::parse($gelombang->tgl_mulai)->format('d M Y') }}
                            -
                            {{ \Carbon\Carbon::parse($gelombang->tgl_akhir)->format('d M Y') }}
                        </div>
                    </div>

                    <div class="d-flex align-items-center top-action">

                        {{-- BUTTON DAFTAR --}}
                        @if(!$pendaftaran)
                            <form action="{{ route('pendaftaran-kkn.store') }}" method="POST">
                                @csrf

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Daftar Program
                                </button>
                            </form>
                        @endif

                        {{-- BUTTON PLOTTING --}}
                        @if($pendaftaran && !$kelompok)

                            <a href="{{ route('war.index') }}"
                               class="btn btn-warning">

                                <i class="fas fa-fist-raised mr-1"></i>
                                Masuk WAR KKN

                            </a>

                        @endif

                    </div>

                </div>


                {{-- BELUM DAFTAR --}}
                @if(!$pendaftaran)

                    <div class="empty-program">
                        <i class="fas fa-user-plus"></i>

                        <h5>Belum Mengikuti Program KKN</h5>

                        <p>
                            Anda belum terdaftar pada program KKN aktif.
                            Klik tombol <strong>Daftar Program</strong>
                            untuk mengikuti gelombang KKN saat ini.
                        </p>
                    </div>

                @endif


                {{-- SUDAH DAFTAR TAPI BELUM PLOTTING --}}
                @if($pendaftaran && !$kelompok)

                    <div class="empty-program">
                        <i class="fas fa-users"></i>

                        <h5>Menunggu Plotting Kelompok</h5>

                        <p class="mb-4">
                            Anda sudah terdaftar pada program KKN.
                            Silakan lakukan plotting kelompok ketika sesi pemilihan kelompok dibuka.
                        </p>

                        <div class="alert alert-warning text-left mx-auto" style="max-width: 700px;">
                            Halaman ini digunakan dalam proses seleksi dan plotting
                            untuk penempatan kelompok KKN mahasiswa.
                        </div>
                    </div>

                @endif


                {{-- SUDAH DAPAT KELOMPOK --}}
                @if($kelompok)

                    <div class="card-body pt-0">

                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

                            <div class="kode-box">
                                {{ $kelompok->kode_kelompok }}
                            </div>

                            <div class="mt-2 mt-md-0">

                                @if($gelombang->status === 'selesai')
                                    <span class="badge badge-success status-badge">
                                        Selesai
                                    </span>
                                @elseif($gelombang->status === 'berjalan')
                                    <span class="badge badge-primary status-badge">
                                        Sedang Berjalan
                                    </span>
                                @else
                                    <span class="badge badge-warning status-badge">
                                        Persiapan
                                    </span>
                                @endif

                            </div>

                        </div>


                        <div class="table-responsive">

                            <table class="table custom-table">

                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Nama Program</th>
                                        <th>Informasi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr>

                                        <td>1</td>

                                        <td style="min-width: 220px;">

                                            <div class="program-name">
                                                {{ $gelombang->nama_gelombang }}
                                            </div>

                                            <div class="program-location">
                                                {{ $kelompok->desaGelombang?->desa?->nama_desa ?? '-' }}
                                                <br>

                                                {{ $kelompok->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-' }}
                                            </div>

                                        </td>

                                        <td style="min-width: 380px;">

                                            <div class="program-meta">

                                                <strong>Kode Kelompok:</strong>
                                                {{ $kelompok->kode_kelompok }}

                                                <br>

                                                <strong>Peserta:</strong>
                                                {{ $kelompok->pesertaKkn->count() }}

                                                <br>

                                                <strong>Pembimbing:</strong>
                                                {{ $kelompok->dosenPembimbingLapangan?->user?->name ?? '-' }}

                                                <br>

                                                <strong>Ketua:</strong>

                                                {{ $kelompok->ketua?->name ?? '-' }}

                                            </div>

                                        </td>

                                        <td>

                                            @if($gelombang->status === 'selesai')
                                                <span class="badge badge-success">
                                                    Selesai
                                                </span>
                                            @elseif($gelombang->status === 'berjalan')
                                                <span class="badge badge-primary">
                                                    Berjalan
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    Persiapan
                                                </span>
                                            @endif

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        <div class="text-right mt-3">
                            <a href="{{ route('kelompok.index') }}"
                               class="btn btn-primary"
                               style="border-radius:10px;font-weight:600;padding:8px 22px;">
                                <i class="fas fa-users mr-1"></i> Lihat Detail Kelompok
                            </a>
                        </div>

                    </div>

                @endif

            </div>

        @endif

    </div>
</section>
@endsection
