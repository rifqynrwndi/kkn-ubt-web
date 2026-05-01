@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Detail Biodata Mahasiswa</h1>
    </div>

    <div class="section-body">
        <div class="card">

            <div class="card-header">
                <h4>{{ $mahasiswa->name }}</h4>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3 text-center mb-4">
                        <img
                            src="{{ $mahasiswa->mahasiswa?->foto
                                ? asset('storage/'.$mahasiswa->mahasiswa->foto)
                                : asset('img/avatar/avatar-1.png') }}"
                            class="rounded-circle shadow"
                            width="180"
                            height="180"
                            style="object-fit: cover;"
                        >
                    </div>

                    <div class="col-md-9">
                        <table class="table table-borderless">

                            <tr>
                                <th width="30%">Nama Lengkap</th>
                                <td>{{ $mahasiswa->name }}</td>
                            </tr>

                            <tr>
                                <th>Email</th>
                                <td>{{ $mahasiswa->email }}</td>
                            </tr>

                            <tr>
                                <th>Status Email</th>
                                <td>
                                    @if($mahasiswa->email_verified_at)
                                        <span class="badge badge-success">
                                            Verified
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            Not Verified
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>NPM</th>
                                <td>{{ $mahasiswa->mahasiswa?->npm ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Jenis Kelamin</th>
                                <td>
                                    {{ $mahasiswa->mahasiswa?->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Program Studi</th>
                                <td>{{ $mahasiswa->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>No HP</th>
                                <td>{{ $mahasiswa->mahasiswa?->no_hp ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Nama Orang Tua / Wali</th>
                                <td>{{ $mahasiswa->mahasiswa?->nama_ortu ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>No HP Orang Tua</th>
                                <td>{{ $mahasiswa->mahasiswa?->no_hp_ortu ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Alamat Orang Tua</th>
                                <td>{{ $mahasiswa->mahasiswa?->alamat_ortu ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Status Biodata</th>
                                <td>
                                    @if($mahasiswa->mahasiswa?->is_biodata_complete)
                                        <span class="badge badge-success">
                                            Lengkap
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            Belum Lengkap
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        </table>
                    </div>

                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary ">
                    Kembali
                </a>

                <a href="{{ route('mahasiswa.edit', $mahasiswa->id) }}"
                   class="btn btn-primary">
                    Edit Mahasiswa
                </a>
            </div>

        </div>
    </div>
</section>
@endsection
