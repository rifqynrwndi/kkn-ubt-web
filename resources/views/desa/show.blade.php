@extends('layouts.app')

@section('title', 'Detail Desa')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Detail Desa</h1>

        <div>
            <a href="{{ route('desa.edit', $desa->id) }}"
               class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i>
                Edit
            </a>

            <a href="{{ route('desa.index') }}"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="section-body">

        {{-- INFORMASI DESA --}}
        <div class="card shadow-sm mb-4">

            <div class="card-header">
                <h4>Informasi Desa</h4>
            </div>

            <div class="card-body">

                <div class="row">

                    {{-- NAMA DESA --}}
                    <div class="col-md-4 mb-4">
                        <small class="text-muted d-block">
                            Nama Desa
                        </small>

                        <strong class="h6">
                            {{ $desa->nama_desa }}
                        </strong>
                    </div>

                    {{-- KECAMATAN --}}
                    <div class="col-md-4 mb-4">
                        <small class="text-muted d-block">
                            Kecamatan
                        </small>

                        <strong class="h6">
                            {{ $desa->kecamatan->nama_kecamatan }}
                        </strong>
                    </div>

                    {{-- STATUS --}}
                    <div class="col-md-4 mb-4">
                        <small class="text-muted d-block">
                            Status
                        </small>

                        <div>
                            @if($desa->aktif)
                                <span class="badge badge-success px-3 py-2">
                                    Aktif
                                </span>
                            @else
                                <span class="badge badge-secondary px-3 py-2">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- STATISTIK --}}
        <div class="row">

            <div class="col-md-4">
                <div class="card shadow-sm">

                    <div class="card-body text-center">
                        <h6 class="text-muted">
                            Jumlah Gelombang
                        </h6>

                        <h2 class="mb-0">
                            {{ $jumlahGelombang }}
                        </h2>
                    </div>

                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">

                    <div class="card-body text-center">
                        <h6 class="text-muted">
                            Total Kuota
                        </h6>

                        <h2 class="mb-0">
                            {{ $totalKuota }}
                        </h2>
                    </div>

                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">

                    <div class="card-body text-center">
                        <h6 class="text-muted">
                            Gelombang Aktif
                        </h6>

                        <h2 class="mb-0">
                            {{ $desaGelombangAktif->count() }}
                        </h2>
                    </div>

                </div>
            </div>

        </div>

        {{-- PENEMPATAN GELOMBANG --}}
        <div class="card shadow-sm mt-4">

            <div class="card-header">
                <h4>Penempatan Gelombang</h4>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Gelombang</th>
                                <th>Kuota</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($desa->desaGelombang as $item)

                                <tr>

                                    <td>
                                        {{ $item->gelombang->nama_gelombang ?? '-' }}
                                    </td>

                                    <td>
                                        <span class="badge badge-info">
                                            {{ $item->kuota_total }} Orang
                                        </span>
                                    </td>

                                    <td>
                                        @if($item->status === 'dibuka')
                                            <span class="badge badge-success">
                                                Dibuka
                                            </span>
                                        @elseif($item->status === 'penuh')
                                            <span class="badge badge-danger">
                                                Penuh
                                            </span>
                                        @elseif($item->status === 'ditutup')
                                            <span class="badge badge-secondary">
                                                Ditutup
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                Draft
                                            </span>
                                        @endif
                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3"
                                        class="text-center text-muted py-4">
                                        Belum ada penempatan gelombang.
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
