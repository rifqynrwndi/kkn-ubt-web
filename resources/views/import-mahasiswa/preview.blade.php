@extends('layouts.app')

@section('title', 'Preview Import Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Preview Import Mahasiswa</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('import-mahasiswa.index') }}">Import</a></div>
            <div class="breadcrumb-item active">Preview</div>
        </div>
    </div>

    <div class="section-body">

        @if(count($errors) > 0)
        <div class="alert alert-warning">
            <strong>{{ count($errors) }} baris bermasalah ditemukan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-table mr-2 text-primary"></i> Data Preview</h4>
                <div>
                    <span class="badge badge-info mr-2">{{ $totalRows }} total</span>
                    <span class="badge badge-success">{{ $newRows }} baru</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="50">#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>NPM</th>
                                <th>Gender</th>
                                <th>Prodi</th>
                                <th width="100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $row['name'] }}</td>
                                <td>{{ $row['email'] }}</td>
                                <td>{{ $row['npm'] }}</td>
                                <td>{{ $row['gender'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $row['prodi_text'] ?? $row['prodi_id'] ?? '-' }}</td>
                                <td>
                                    @if($row['exists'] === 'Baru')
                                        <span class="badge badge-success">Baru</span>
                                    @else
                                        <span class="badge badge-warning">Sudah Ada</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data valid.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($newRows > 0)
        <div class="card">
            <div class="card-body">
                <form action="{{ route('import-mahasiswa.import') }}" method="POST">
                    @csrf
                    <input type="hidden" name="gelombang_id" value="{{ $gelombang->id }}">
                    <input type="hidden" name="data" value="{{ json_encode(array_filter($rows, fn($r) => $r['exists'] === 'Baru')) }}">
                    <div class="alert alert-info">
                        <strong>{{ $newRows }} mahasiswa baru</strong> akan diimport ke gelombang
                        <strong>{{ $gelombang->nama_gelombang }}</strong>.
                        Mahasiswa yang sudah ada akan dilewati.
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Import {{ $newRows }} mahasiswa sekarang?')">
                            <i class="fas fa-check mr-1"></i> Konfirmasi Import
                        </button>
                        <a href="{{ route('import-mahasiswa.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-3">Semua data sudah ada di sistem. Tidak ada yang perlu diimport.</p>
                <a href="{{ route('import-mahasiswa.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
        @endif

    </div>
</section>
@endsection
