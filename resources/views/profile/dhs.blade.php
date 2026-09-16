@extends('layouts.app')

@section('title', 'Upload DHS')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Daftar Hasil Studi (DHS)</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                {{ session('info') }}
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                            </div>
                        @endif

                        {{-- Status Badge --}}
                        <div class="mb-4">
                            <label class="text-muted small d-block mb-1">Status DHS</label>
                            @if($mahasiswa->dhs_status === 'verified')
                                <span class="badge badge-success" style="font-size: 13px; padding: 6px 12px;">
                                    <i class="fas fa-check-circle mr-1"></i> Terverifikasi
                                </span>
                            @elseif($mahasiswa->dhs_status === 'rejected')
                                <span class="badge badge-danger" style="font-size: 13px; padding: 6px 12px;">
                                    <i class="fas fa-times-circle mr-1"></i> Ditolak
                                </span>
                            @else
                                <span class="badge badge-warning" style="font-size: 13px; padding: 6px 12px;">
                                    <i class="fas fa-clock mr-1"></i> Menunggu Verifikasi
                                </span>
                            @endif
                        </div>

                        {{-- Rejection Note --}}
                        @if($mahasiswa->dhs_status === 'rejected' && $mahasiswa->dhs_catatan)
                            <div class="alert alert-danger">
                                <strong>Catatan Admin:</strong><br>
                                {{ $mahasiswa->dhs_catatan }}
                            </div>
                        @endif

                        {{-- Upload Form --}}
                        @if($mahasiswa->dhs_status !== 'verified')
                            <form action="{{ route('profile.dhs.store') }}" method="POST" enctype="multipart/form-data" id="dhs-form">
                                @csrf

                                <div class="form-group">
                                    <label for="dhs_file"><strong>Upload DHS (PDF only, maks 2MB)</strong></label>
                                    <input type="file"
                                           name="dhs_file"
                                           id="dhs_file"
                                           class="form-control @error('dhs_file') is-invalid @enderror"
                                           accept=".pdf"
                                           required>
                                    @error('dhs_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Preview --}}
                                <div id="file-preview" class="d-none mb-3">
                                    <label class="text-muted small">Preview:</label>
                                    <div class="border rounded p-2" style="background: #f9fafb;">
                                        <i class="fas fa-file-pdf text-danger mr-2"></i>
                                        <span id="file-name"></span>
                                        <span id="file-size" class="text-muted ml-2"></span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-upload mr-1"></i> Unggah DHS
                                </button>
                            </form>
                        @else
                            {{-- Already verified --}}
                            <div class="text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                DHS Anda sudah terverifikasi. Tidak perlu mengunggah ulang.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Panduan</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0" style="font-size: 13px;">
                            <li class="mb-2">
                                <i class="fas fa-file-pdf text-danger mr-1"></i>
                                Format: <strong>PDF only</strong>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-weight-hanging text-muted mr-1"></i>
                                Ukuran: <strong>maks 2MB</strong>
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success mr-1"></i>
                                Pastikan DHS masih berlaku
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-eye text-muted mr-1"></i>
                                Isi DHS harus terbaca jelas
                            </li>
                            <li>
                                <i class="fas fa-clock text-warning mr-1"></i>
                                Verifikasi oleh admin dalam 1x24 jam
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('dhs_file')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');

    if (file) {
        preview.classList.remove('d-none');
        fileName.textContent = file.name;
        fileSize.textContent = '(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    } else {
        preview.classList.add('d-none');
    }
});
</script>
@endpush
