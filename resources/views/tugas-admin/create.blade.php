@extends('layouts.app')
@section('title', 'Tambah Tugas Kelompok')
@section('content')
<section class="section">
    <div class="section-header">
        <h1>Tambah Tugas Kelompok</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.tugas.index') }}">Tugas Kelompok</a></div>
            <div class="breadcrumb-item active">Tambah</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h4>Form Tugas</h4></div>
                    <div class="card-body">
                        <form action="{{ route('admin.tugas.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Nama Tugas</label>
                                <input name="nama_tugas" class="form-control" placeholder="Masukkan nama tugas..." required>
                            </div>
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori" class="form-control" required>
                                    <option value="tugas_kelompok">Tugas Kelompok</option>
                                    <option value="luaran_wajib">Luaran Wajib</option>
                                    <option value="luaran_lain">Luaran Lain</option>
                                    <option value="laporan">Laporan</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                            <a href="{{ route('admin.tugas.index') }}" class="btn btn-secondary">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Pilih Kelompok</h4>
                        <label class="mb-0"><input type="checkbox" id="select-all" onclick="document.querySelectorAll('.kl-check').forEach(c=>c.checked=this.checked)"> Pilih Semua</label>
                    </div>
                    <div class="card-body" style="max-height:500px;overflow-y:auto;">
                        @foreach($kelompoks as $k)
                        <div class="form-check">
                            <input type="checkbox" name="kelompok_ids[]" value="{{ $k->id }}" class="kl-check form-check-input" id="kl-{{ $k->id }}">
                            <label class="form-check-label" for="kl-{{ $k->id }}">
                                <strong>{{ $k->nama_kelompok }}</strong>
                                <br><small class="text-muted">{{ $k->desaGelombang->desa->nama_desa ?? '-' }}, {{ $k->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}</small>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
