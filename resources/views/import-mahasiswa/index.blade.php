@extends('layouts.app')

@section('title', 'Import Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Import Mahasiswa</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Import Mahasiswa</div>
        </div>
    </div>

    <div class="section-body">
        <ul class="nav nav-tabs mb-4" id="importTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#csv-tab">CSV Import</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#sql-tab">SQL Import (DB Lama)</a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- CSV TAB --}}
            <div class="tab-pane fade show active" id="csv-tab">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-file-csv mr-2 text-primary"></i> Upload File CSV</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Format CSV:</strong> name, email, npm, jenis_kelamin (L/P), prodi_id<br>
                            <strong>Catatan:</strong> Password default = NPM. Mahasiswa langsung approved + masuk gelombang terpilih.
                        </div>

                        <form action="{{ route('import-mahasiswa.preview') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Pilih Gelombang KKN</label>
                                <select name="gelombang_id" class="form-control" required>
                                    <option value="">-- Pilih Gelombang --</option>
                                    @foreach($gelombangs as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama_gelombang }} ({{ $g->tahun }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>File CSV</label>
                                <input type="file" name="file" class="form-control-file" accept=".csv,.txt" required>
                                <small class="text-muted">Hanya .csv atau .txt</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-1"></i> Preview Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- SQL TAB --}}
            <div class="tab-pane fade" id="sql-tab">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-database mr-2 text-primary"></i> Import dari Database Lama</h4>
                    </div>
                    <div class="card-body">
                        @if(!$hasOldConnection)
                            <div class="alert alert-warning">
                                <strong>Koneksi ke database lama belum dikonfigurasi.</strong><br>
                                Tambahkan koneksi <code>old_mysql</code> di <code>config/database.php</code> dan <code>.env</code>.<br>
                                Contoh di <code>.env</code>:<br>
                                <code>DB_OLD_HOST=xxx</code><br>
                                <code>DB_OLD_DATABASE=xxx</code><br>
                                <code>DB_OLD_USERNAME=xxx</code><br>
                                <code>DB_OLD_PASSWORD=xxx</code>
                            </div>
                        @else
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle mr-1"></i> Koneksi ke database lama berhasil.
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <strong>Cara kerja:</strong> Membaca data <code>users</code> dan <code>mahasiswa</code> dari database lama,
                            melakukan mapping prodi & fakultas ke ID baru, lalu import sebagai mahasiswa approved.
                        </div>

                        <form action="{{ route('import-mahasiswa.sql-preview') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Pilih Gelombang KKN</label>
                                <select name="gelombang_id" class="form-control" required>
                                    <option value="">-- Pilih Gelombang --</option>
                                    @foreach($gelombangs as $g)
                                        <option value="{{ $g->id }}">{{ $g->nama_gelombang }} ({{ $g->tahun }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" {{ !$hasOldConnection ? 'disabled' : '' }}>
                                <i class="fas fa-search mr-1"></i> Preview Data dari DB Lama
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
