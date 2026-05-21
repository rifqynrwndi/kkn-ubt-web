@extends('layouts.app')

@section('title', 'Biodata Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Biodata Mahasiswa</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('dpl.kelompok.index') }}">Kelompok Binaan</a></div>
            <div class="breadcrumb-item"><a href="{{ route('dpl.kelompok.show', $peserta->kelompokKkn->id) }}">{{ $peserta->kelompokKkn->nama_kelompok }}</a></div>
            <div class="breadcrumb-item active">Biodata</div>
        </div>
    </div>

    <div class="section-body">
        @php $m = $peserta->mahasiswa; @endphp
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="{{ $m->foto ? asset('storage/'.$m->foto) : asset('img/avatar/avatar-1.png') }}"
                             class="rounded-circle shadow mb-3"
                             width="120" height="120" style="object-fit:cover;">
                        <h5 class="font-weight-bold">{{ $m->user->name ?? '-' }}</h5>
                        <p class="text-muted mb-1">{{ $m->npm }}</p>
                        <span class="badge badge-info">{{ $m->prodi->fakultas->nama_fakultas ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h4>Data Diri</h4></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th width="160">Nama Lengkap</th><td>{{ $m->user->name ?? '-' }}</td></tr>
                            <tr><th>NPM</th><td>{{ $m->npm ?? '-' }}</td></tr>
                            <tr><th>Email</th><td>{{ $m->user->email ?? '-' }}</td></tr>
                            <tr><th>Jenis Kelamin</th><td>{{ $m->jenis_kelamin === 'L' ? 'Laki-laki' : ($m->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td></tr>
                            <tr><th>No HP</th><td>{{ $m->no_hp ?? '-' }}</td></tr>
                            <tr><th>Program Studi</th><td>{{ $m->prodi->nama_prodi ?? '-' }}</td></tr>
                            <tr><th>Fakultas</th><td>{{ $m->prodi->fakultas->nama_fakultas ?? '-' }}</td></tr>
                            <tr><th>Kelompok</th><td>{{ $peserta->kelompokKkn->nama_kelompok ?? '-' }}</td></tr>
                            <tr><th>Status Pendaftaran</th><td><span class="badge badge-success">{{ $peserta->status_pendaftaran }}</span></td></tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h4>Data Orang Tua</h4></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th width="160">Nama Orang Tua</th><td>{{ $m->nama_ortu ?? '-' }}</td></tr>
                            <tr><th>No HP Orang Tua</th><td>{{ $m->no_hp_ortu ?? '-' }}</td></tr>
                            <tr><th>Alamat Orang Tua</th><td>{{ $m->alamat_ortu ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
