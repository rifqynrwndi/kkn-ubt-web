@extends('layouts.app')

@section('title', 'Export DPL Artikel')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Export DPL Artikel</h1>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Gelombang</label>
                    <select name="gelombang_id" class="form-select">
                        <option value="">Semua Gelombang</option>
                        @foreach($gelombangs as $g)
                            <option value="{{ $g->id }}" {{ request('gelombang_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->nama_gelombang }} ({{ $g->tahun }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DPL</label>
                    <select name="dpl_id" class="form-select">
                        <option value="">Semua DPL</option>
                        @foreach($dpls as $d)
                            <option value="{{ $d->id }}" {{ request('dpl_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jenis</label>
                    <select name="jenis" class="form-select">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisOptions as $key => $label)
                            <option value="{{ $key }}" {{ request('jenis') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Cari Judul/Deskripsi</label>
                    <input type="text" name="search" class="form-control" placeholder="Kata kunci..." value="{{ request('search') }}">
                </div>

                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter me-1"></i> Terapkan Filter
                    </button>
                    <a href="{{ route('admin.export.dpl-artikel') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                    <a href="{{ route('admin.export.dpl-artikel.excel', request()->query()) }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.export.dpl-artikel.zip', request()->query()) }}" class="btn btn-warning text-dark">
                        <i class="bi bi-file-earmark-zip me-1"></i> Download ZIP
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Laporan DPL ({{ $laporans->total() }} data)</h5>
        </div>
        <div class="card-body p-0">
            @if($laporans->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <p class="text-muted mt-2">Tidak ada data laporan DPL.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>DPL</th>
                                <th>Kelompok</th>
                                <th>Desa</th>
                                <th>Jenis</th>
                                <th>Judul</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($laporans as $index => $laporan)
                                <tr>
                                    <td>{{ $laporans->firstItem() + $index }}</td>
                                    <td>{{ $laporan->created_at->format('d-m-Y H:i') }}</td>
                                    <td>{{ $laporan->dpl?->user?->name ?? '-' }}</td>
                                    <td>{{ $laporan->kelompokKkn?->nama_kelompok ?? '-' }}</td>
                                    <td>{{ $laporan->kelompokKkn?->desaGelombang?->desa?->nama_desa ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info-emphasis">
                                            {{ $jenisOptions[$laporan->jenis] ?? $laporan->jenis }}
                                        </span>
                                    </td>
                                    <td>{{ $laporan->judul ?? '-' }}</td>
                                    <td>
                                        @if($laporan->file_path)
                                            <a href="{{ url('storage/' . $laporan->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download me-1"></i> Unduh
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $laporans->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection