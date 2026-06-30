@extends('layouts.app')
@section('title', isset($editing) ? 'Edit Log Book' : 'Tambah Log Book')
@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ isset($editing) ? 'Edit' : 'Tambah' }} Log Book</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('kelompok.index', ['tab' => 'logbook']) }}">Log Book</a></div>
            <div class="breadcrumb-item active">{{ isset($editing) ? 'Edit' : 'Tambah' }}</div>
        </div>
    </div>
    <div class="section-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom">
                        <h4 class="mb-0"><i class="fas fa-book mr-2"></i>{{ isset($editing) ? 'Edit' : 'Form' }} Log Book</h4>
                    </div>
                    <div class="card-body">
                        @if(isset($editing))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Log book ini ditolak oleh DPL. Silakan perbaiki dan kirim ulang.
                            @if($editing->komentar_dpl)<br><strong>Komentar DPL:</strong> {{ $editing->komentar_dpl }}@endif
                        </div>
                        @endif
                        <form action="{{ isset($editing) ? route('kelompok.logbook.update', $editing->id) : route('kelompok.logbook.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(isset($editing)) @method('PUT') @endif
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Kegiatan</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" max="{{ date('Y-m-d') }}" value="{{ old('tanggal', isset($editing) ? $editing->tanggal->format('Y-m-d') : date('Y-m-d')) }}" required>
                                </div>
                                @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Judul Kegiatan</label>
                                <input name="judul" class="form-control @error('judul') is-invalid @enderror" placeholder="Masukkan judul kegiatan..." value="{{ old('judul', $editing->judul ?? '') }}" required>
                                @error('judul') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Deskripsi Kegiatan <small class="text-muted font-weight-normal">(min 50 karakter)</small></label>
                                <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="6" placeholder="Ceritakan kegiatan yang dilakukan..." required>{{ old('deskripsi', $editing?->deskripsi ?? '') }}</textarea>
                                <small id="char-counter" class="d-block mt-1">0 / 50 karakter</small>
                                @error('deskripsi') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold">Lampiran <small class="text-muted font-weight-normal">(Opsional)</small></label>
                                @if(isset($editing) && $editing->file_path)
                                @php $ext = pathinfo($editing->file_path, PATHINFO_EXTENSION); @endphp
                                @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                <div class="mb-2">
                                    <img src="{{ storage_url($editing->file_path) }}" class="rounded shadow-sm" style="max-width:120px;max-height:120px;object-fit:cover;">
                                    <small class="text-muted d-block mt-1">Upload baru untuk mengganti.</small>
                                </div>
                                @else
                                <div class="mb-2">
                                    <a href="{{ storage_url($editing->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download mr-1"></i>{{ $editing->file_name ?? 'File' }}</a>
                                    <small class="text-muted d-block mt-1">Upload baru untuk mengganti.</small>
                                </div>
                                @endif
                                @endif
                                <input type="file" name="file" class="form-control-file @error('file') is-invalid @enderror" accept="image/*,.pdf">
                                <small class="text-muted d-block mt-1">JPG, PNG, atau PDF — Maks 5MB</small>
                                @error('file') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <hr>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('kelompok.index', ['tab' => 'logbook']) }}" class="btn btn-outline-secondary"><i class="fas fa-times mr-1"></i> Batal</a>
                                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-1"></i> {{ isset($editing) ? 'Update' : 'Simpan' }}</button>
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
