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
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-upload mr-2 text-primary"></i> Upload File CSV</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Format CSV:</strong> name, email, npm, jenis_kelamin (L/P), prodi_id (atau nama_prodi)<br>
                    <strong>Catatan:</strong> Password default menggunakan NPM. Mahasiswa akan langsung disetujui dan dimasukkan ke gelombang yang dipilih.
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
                        <small class="text-muted">Hanya file .csv atau .txt</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-1"></i> Preview Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
