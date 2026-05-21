@extends('layouts.app')

@section('title', 'Detail Kelompok — ' . $kelompok->nama_kelompok)

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $kelompok->nama_kelompok }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('dpl.kelompok.index') }}">Kelompok Binaan</a></div>
            <div class="breadcrumb-item active">Detail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header"><h4>Informasi Kelompok</h4></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th width="140">Nama Kelompok</th><td>{{ $kelompok->nama_kelompok }}</td></tr>
                            <tr><th>Kode</th><td>{{ $kelompok->kode_kelompok }}</td></tr>
                            <tr><th>Desa</th><td>{{ $kelompok->desaGelombang->desa->nama_desa ?? '-' }}</td></tr>
                            <tr><th>Kecamatan</th><td>{{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}</td></tr>
                            <tr><th>Kabupaten</th><td>{{ $kelompok->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}</td></tr>
                            <tr><th>Kuota</th><td>{{ $kelompok->pesertaKkn->count() }} / {{ $kelompok->kuota }}</td></tr>
                            <tr><th>Ketua</th><td>{{ $kelompok->ketua?->mahasiswa?->user?->name ?? 'Belum ditentukan' }}</td></tr>
                            <tr><th>Status</th><td><span class="badge badge-{{ $kelompok->status === 'penuh' ? 'danger' : 'success' }}">{{ $kelompok->status }}</span></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Anggota Kelompok</h4>
                        <span class="badge badge-primary">{{ $kelompok->pesertaKkn->count() }} orang</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Nama</th>
                                        <th>NPM</th>
                                        <th>Prodi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelompok->pesertaKkn as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('dpl.mahasiswa.show', $p->id) }}">
                                                {{ $p->mahasiswa?->user?->name ?? '-' }}
                                            </a>
                                            @if($p->id === $kelompok->ketua_peserta_id)
                                                <span class="badge badge-warning ml-1">Ketua</span>
                                            @endif
                                        </td>
                                        <td>{{ $p->mahasiswa?->npm ?? '-' }}</td>
                                        <td>{{ $p->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                                        <td>
                                            @if($p->status_pendaftaran === 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-warning">{{ $p->status_pendaftaran }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada anggota.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
