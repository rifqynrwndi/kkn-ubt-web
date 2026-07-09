@extends('layouts.app')
@section('title', 'Edit Tugas')
@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Tugas</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.tugas.index') }}">Tugas Kelompok</a></div>
            <div class="breadcrumb-item active">Edit</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h4 class="mb-0"><i class="fas fa-edit mr-2"></i>Edit Tugas</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.tugas.updateByNama') }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="old_nama" value="{{ $tugas->nama_tugas }}">
                            <div class="form-group">
                                <label class="font-weight-bold">Nama Tugas</label>
                                <input name="nama_tugas" class="form-control @error('nama_tugas') is-invalid @enderror" value="{{ old('nama_tugas', $tugas->nama_tugas) }}" required>
                                @error('nama_tugas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Kategori</label>
                                <select name="kategori" class="form-control @error('kategori') is-invalid @enderror" required>
                                    <option value="tugas_kelompok" {{ $tugas->kategori=='tugas_kelompok'?'selected':'' }}>Tugas Kelompok</option>
                                    <option value="luaran_wajib" {{ $tugas->kategori=='luaran_wajib'?'selected':'' }}>Luaran Wajib</option>
                                    <option value="luaran_lain" {{ $tugas->kategori=='luaran_lain'?'selected':'' }}>Luaran Tambahan</option>
                                    <option value="laporan" {{ $tugas->kategori=='laporan'?'selected':'' }}>Laporan</option>
                                </select>
                                @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                                <a href="{{ route('admin.tugas.index') }}" class="btn btn-outline-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
