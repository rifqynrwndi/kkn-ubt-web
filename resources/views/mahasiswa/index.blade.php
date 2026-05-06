@extends('layouts.app')

@section('title', 'Manajemen Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between">
        <h1>Data Mahasiswa</h1>

        <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Mahasiswa
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <form method="GET" class="mb-3">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Cari nama / npm / email..."
                       value="{{ request('search') }}">
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Email</th>
                            <th>Verifikasi Email</th>
                            <th>Biodata</th>
                            <th>Daftar Gelombang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mahasiswas as $mhs)
                        <tr>
                            <td>{{ $loop->iteration + ($mahasiswas->firstItem() - 1) }}</td>
                            <td>{{ $mhs->name }}</td>
                            <td>{{ $mhs->mahasiswa?->npm }}</td>
                            <td>{{ $mhs->email }}</td>

                            <td>
                                @if($mhs->email_verified_at)
                                    <span class="badge badge-success">Verified</span>
                                @else
                                    <span class="badge badge-danger">Not Verified</span>
                                @endif
                            </td>

                            <td>
                                @if($mhs->mahasiswa?->is_biodata_complete)
                                    <span class="badge badge-success">Complete</span>
                                @else
                                    <span class="badge badge-warning">Incomplete</span>
                                @endif
                            </td>

                            <td>
                                @if($mhs->mahasiswa?->pesertaKkn->count() > 0)
                                {{-- // buat dia ikut gelombang namanya apa --}}
                                    <span class="badge badge-outline-info">
                                        {{ $mhs->mahasiswa->pesertaKkn->map(fn($p) => $p->gelombang->nama_gelombang)->join(', ') }}
                                    </span>
                                @else
                                    <span class="badge badge-outline-secondary">
                                        Belum Terdaftar
                                    </span>
                                @endif
                            </td>

                            <td class="text-center" style="white-space: nowrap;">
                                <a href="{{ route('mahasiswa.show', $mhs->id) }}"
                                    class="btn btn-info btn-sm"
                                    data-toggle="tooltip"
                                    data-placement="top"
                                    title="Detail Biodata">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('mahasiswa.edit', $mhs) }}" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Edit Mahasiswa">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('mahasiswa.destroy', $mhs) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="Hapus Mahasiswa"
                                            onclick="return confirm('Hapus mahasiswa?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Data kosong</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $mahasiswas->links() }}
        </div>
    </div>
</section>
@endsection
