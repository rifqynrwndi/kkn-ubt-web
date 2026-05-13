@extends('layouts.app')

@section('title', 'Plotting KKN')

@section('content')

<style>
    .center-box {
        min-height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-center {
        max-width: 420px;
        width: 100%;
        text-align: center;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,.05);
    }

    .icon-big {
        font-size: 50px;
        margin-bottom: 15px;
    }

    .warning { color: #fc544b; }
    .waiting { color: #ffa426; }

    .info-box {
        border-left: 4px solid #6777ef;
        background: #f8f9ff;
        padding: 15px;
        border-radius: 8px;
    }

    .badge-soft {
        background: rgba(103,119,239,.12);
        color: #6777ef;
        padding: 6px 10px;
        border-radius: 6px;
        font-weight: 600;
    }

    .table td, .table th {
        vertical-align: middle !important;
    }
</style>

<section class="section">

    <div class="section-header">
        <h1>Plotting Kelompok KKN</h1>
    </div>

    <div class="section-body">

        {{-- ALERT --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- STATE 1 --}}
        @if(!$documentUploadComplete)

            <div class="center-box">
                <div class="card card-center">

                    <div class="icon-big warning">
                        <i class="fas fa-file-alt"></i>
                    </div>

                    <h4>Dokumen Belum Lengkap</h4>
                    <p class="text-muted">
                        Silakan upload seluruh dokumen KKN terlebih dahulu.
                    </p>

                    <a href="{{ route('dokumen-pendaftaran.index') }}"
                       class="btn btn-danger btn-block mt-3">
                        Lengkapi Dokumen
                    </a>

                </div>
            </div>

        {{-- STATE 2 --}}
        @elseif(!$documentVerified)

            <div class="center-box">
                <div class="card card-center">

                    <div class="icon-big waiting">
                        <i class="fas fa-hourglass-half"></i>
                    </div>

                    <h4>Menunggu Verifikasi</h4>
                    <p class="text-muted">
                        Dokumen Anda sedang diperiksa oleh admin.
                    </p>

                    <a href="{{ route('dokumen-pendaftaran.index') }}"
                       class="btn btn-warning btn-block mt-3">
                        Cek Status Dokumen
                    </a>

                </div>
            </div>

        {{-- STATE 3 --}}
        @else

            <div class="alert alert-success">
                Dokumen Anda telah diverifikasi. Silakan pilih kelompok KKN yang tersedia.
            </div>

            <div class="card">

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-striped">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Lokasi KKN</th>
                                    <th>DPL</th>
                                    <th>Anggota</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($kelompoks as $kelompok)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>

                                            <strong>
                                                {{ $kelompok->desaGelombang->desa->nama_desa }}
                                            </strong>

                                            <br>

                                            <small class="text-muted">
                                                {{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan }},
                                                {{ $kelompok->desaGelombang->desa->kecamatan->kabupaten }}
                                            </small>

                                        </td>

                                        <td>
                                            {{ $kelompok->dosenPembimbingLapangan->user->name ?? '-' }}
                                        </td>

                                        <td style="min-width: 180px;">

                                            <div class="d-flex justify-content-between mb-1">
                                                <small>Terisi</small>
                                                <small>{{ $kelompok->terisi }}/{{ $kelompok->kuota }}</small>
                                            </div>

                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-success"
                                                    style="width: {{ ($kelompok->terisi / $kelompok->kuota) * 100 }}%">
                                                </div>
                                            </div>

                                        </td>

                                        <td>
                                            @if($kelompok->is_full)
                                                <span class="badge badge-danger">Penuh</span>
                                            @else
                                                <span class="badge badge-success">Tersedia</span>
                                            @endif
                                        </td>

                                        <td>

                                            @if($kelompok->is_full)

                                                <button class="btn btn-secondary btn-sm" disabled>
                                                    Penuh
                                                </button>

                                            @else

                                                <form action="{{ route('pendaftaran-kkn.ambil-kelompok', $kelompok->id) }}"
                                                      method="POST">
                                                    @csrf

                                                    <button class="btn btn-primary btn-sm"
                                                            onclick="return confirm('Ambil kelompok ini?')">

                                                        Ambil

                                                    </button>

                                                </form>

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            Belum ada kelompok tersedia
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        @endif

    </div>

</section>

@endsection
