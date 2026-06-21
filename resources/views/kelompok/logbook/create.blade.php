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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom">
                        <h4 class="mb-0"><i class="fas fa-book mr-2"></i>Form Log Book</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('kelompok.logbook.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Kegiatan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" max="{{ date('Y-m-d') }}" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                </div>
                                @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Judul Kegiatan</label>
                                <input name="judul" class="form-control @error('judul') is-invalid @enderror" placeholder="Masukkan judul kegiatan..." value="{{ old('judul') }}" required>
                                @error('judul') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Deskripsi Kegiatan <small class="text-muted font-weight-normal">(min 50 karakter)</small></label>
                                <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="6" placeholder="Ceritakan kegiatan yang dilakukan..." required>{{ old('deskripsi') }}</textarea>
                                <small id="char-counter" class="d-block mt-1">0 / 50 karakter</small>
                                @error('deskripsi') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Lampiran <small class="text-muted font-weight-normal">(Opsional)</small></label>
                                <div class="custom-file">
                                    <input type="file" name="file" class="custom-file-input @error('file') is-invalid @enderror" id="fileInput" accept="image/*,.pdf">
                                    <label class="custom-file-label" for="fileInput">Pilih file...</label>
                                </div>
                                <small class="text-muted d-block mt-1">JPG, PNG, atau PDF — Maks 5MB</small>
                                @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <hr>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('kelompok.index', ['tab' => 'logbook']) }}" class="btn btn-outline-secondary"><i class="fas fa-times mr-1"></i> Batal</a>
                                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> Simpan</button>
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
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var name = e.target.files[0]?.name || 'Pilih file...';
        e.target.nextElementSibling.textContent = name;
    });
    const ta = document.querySelector('textarea[name="deskripsi"]');
    ta.addEventListener('input', function() {
        var len = this.value.length;
        document.getElementById('char-counter').textContent = len + ' / 50 karakter';
        document.getElementById('char-counter').style.color = len >= 50 ? '#47c363' : '#fc544b';
    });
    ta.dispatchEvent(new Event('input'));
</script>
@endpush
