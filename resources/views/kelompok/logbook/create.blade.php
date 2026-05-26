@extends('layouts.app')
@section('title', 'Tambah Log Book')
@section('content')
<section class="section">
    <div class="section-header">
        <h1>Tambah Log Book</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('kelompok.index', ['tab' => 'logbook']) }}">Log Book</a></div>
            <div class="breadcrumb-item active">Tambah</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h4><i class="fas fa-book mr-2"></i> Form Log Book</h4></div>
                    <div class="card-body">
                        <form action="{{ route('kelompok.logbook.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Tanggal Kegiatan</label>
                                <input type="date" name="tanggal" class="form-control" max="{{ date('Y-m-d') }}" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label>Judul Kegiatan</label>
                                <input name="judul" class="form-control" placeholder="Masukkan judul kegiatan..." value="{{ old('judul') }}" required>
                                @error('judul') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label>Deskripsi Kegiatan <small class="text-muted">(min 50 karakter)</small></label>
                                <textarea name="deskripsi" class="form-control" rows="5" placeholder="Ceritakan kegiatan yang dilakukan..." required>{{ old('deskripsi') }}</textarea>
                                <small class="text-muted" id="char-counter">0 / 50 karakter</small>
                                @error('deskripsi') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label>Lampiran (Opsional)</label>
                                <input type="file" name="file" class="form-control-file">
                                <small class="text-muted d-block mt-1">JPG, PNG, atau PDF — Maks 5MB</small>
                                @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Log Book</button>
                                <a href="{{ route('kelompok.index', ['tab' => 'logbook']) }}" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
    const ta = document.querySelector('textarea[name="deskripsi"]');
    ta.addEventListener('input', function() {
        var len = this.value.length;
        document.getElementById('char-counter').textContent = len + ' / 50 karakter';
        document.getElementById('char-counter').style.color = len >= 50 ? '#47c363' : '#fc544b';
    });
    ta.dispatchEvent(new Event('input'));
</script>
@endpush
