@extends('layouts.app')

@section('title', 'Manajemen Mahasiswa')

@section('content')

<style>
    .table td, .table th {
        vertical-align: middle !important;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .name-column {
        white-space: normal !important;
        min-width: 150px;
        max-width: 200px;
        word-break: break-word;
        line-height: 1.4;
    }

    .action-column {
        white-space: nowrap !important;
        min-width: 130px;
    }
</style>

<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Data Mahasiswa</h1>

        <a href="{{ route('mahasiswa.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Mahasiswa
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Search + Filter --}}
            <form method="GET" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama / npm / email..." value="{{ request('search') }}">
                            <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="verified" {{ request('status')=='verified'?'selected':'' }}>Email Verified</option>
                            <option value="unverified" {{ request('status')=='unverified'?'selected':'' }}>Email Unverified</option>
                            <option value="biodata_incomplete" {{ request('status')=='biodata_incomplete'?'selected':'' }}>Biodata Belum Lengkap</option>
                            <option value="no_photo" {{ request('status')=='no_photo'?'selected':'' }}>Belum Upload Foto</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="input-group">
                            <select id="export-gelombang" class="form-control">
                                <option value="">Semua Gelombang</option>
                                @foreach(\App\Models\Gelombang::orderBy('tahun','desc')->get() as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_gelombang }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <a href="{{ route('mahasiswa.export') }}" class="btn btn-success" id="export-btn" onclick="this.href=this.href+'?gelombang_id='+document.getElementById('export-gelombang').value">
                                    <i class="fas fa-download mr-1"></i> Export
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="button" class="btn btn-outline-secondary btn-block" onclick="window.location='{{ route('mahasiswa.index') }}'">
                            <i class="fas fa-sync-alt"></i> Reset
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-md">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Nama</th>
                            <th>NPM</th>
                            <th>Email</th>
                            <th class="text-center">Verifikasi</th>
                            <th class="text-center">Biodata</th>
                            <th>Gelombang</th>
                            <th class="text-center action-column">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($mahasiswas as $mhs)
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration + ($mahasiswas->firstItem() - 1) }}
                            </td>

                            <td class="name-column">
                                {{ $mhs->name }}
                            </td>

                            <td>{{ $mhs->mahasiswa?->npm ?? '-' }}</td>

                            <td>{{ $mhs->email }}</td>

                            <td class="text-center">
                                @if($mhs->email_verified_at)
                                    <span class="badge badge-success">Verified</span>
                                @else
                                    <span class="badge badge-danger">Not Verified</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if($mhs->mahasiswa?->is_biodata_complete)
                                    <span class="badge badge-success">Complete</span>
                                @else
                                    <span class="badge badge-danger">Incomplete</span>
                                @endif
                            </td>

                            <td>
                                @if($mhs->mahasiswa?->pesertaKkn && $mhs->mahasiswa->pesertaKkn->count() > 0)
                                    <span class="badge badge-outline-info">
                                        {{ $mhs->mahasiswa->pesertaKkn
                                            ->map(fn($p) => $p->gelombang->nama_gelombang)
                                            ->join(', ') }}
                                    </span>
                                @else
                                    <span class="badge badge-outline-secondary">
                                        Belum Terdaftar
                                    </span>
                                @endif
                            </td>

                            <td class="text-center action-column">
                                <a href="{{ route('mahasiswa.show', $mhs->id) }}"
                                   class="btn btn-info btn-sm"
                                   data-toggle="tooltip"
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('mahasiswa.edit', $mhs->id) }}"
                                   class="btn btn-warning btn-sm mx-1"
                                   data-toggle="tooltip"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('mahasiswa.destroy', $mhs->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus mahasiswa ini?')"
                                            data-toggle="tooltip"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Data tidak ditemukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4" style="display: flex; justify-content: center;">
                {{ $mahasiswas->links() }}
            </div>

        </div>
    </div>
</section>

@push('scripts')
<script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@endpush

@endsection
