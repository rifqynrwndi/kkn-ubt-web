@extends('layouts.app')

@section('title', 'Tambah Anggota Kelompok')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Tambah Anggota</h1>
            <small class="text-muted">{{ $kelompok_kkn->nama_kelompok }}</small>
        </div>
        <a href="{{ route('kelompok-kkn.show', $kelompok_kkn->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="section-body">
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Pilih Peserta</h4>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="input-group" style="max-width:500px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama mahasiswa..." value="{{ request('search') }}" autofocus>
                        <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
                        @if(request('search'))<div class="input-group-append"><a href="{{ request()->url() }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a></div>@endif
                    </div>
                </form>

                @if($peserta->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Nama</th>
                                <th>NPM</th>
                                <th>Program Studi</th>
                                <th>Fakultas</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($peserta as $index => $item)
                            <tr>
                                <td>{{ ($peserta->currentPage() - 1) * $peserta->perPage() + $index + 1 }}</td>
                                <td><strong>{{ $item->mahasiswa?->user?->name ?? '-' }}</strong></td>
                                <td>{{ $item->mahasiswa?->npm ?? '-' }}</td>
                                <td>{{ $item->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                                <td>{{ $item->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('kelompok-kkn.anggota.store', $kelompok_kkn->id) }}" method="POST" onsubmit="return confirm('Tambahkan peserta ini ke kelompok?')">
                                        @csrf
                                        <input type="hidden" name="peserta_kkn_id" value="{{ $item->id }}">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-plus mr-1"></i> Tambah
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($peserta->hasPages())
                <div class="card-footer">{{ $peserta->links() }}</div>
                @endif
                @else
                <div class="text-center py-5 text-muted">
                    <span style="font-size:48px;display:block;margin-bottom:12px;">🔍</span>
                    <h5>{{ request('search') ? 'Mahasiswa tidak ditemukan' : 'Belum ada peserta tersedia' }}</h5>
                    <p>{{ request('search') ? 'Coba kata kunci lain.' : 'Semua peserta sudah tergabung dalam kelompok.' }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
