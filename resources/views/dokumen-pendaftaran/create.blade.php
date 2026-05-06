@extends('layouts.app')

@section('title', 'Upload Dokumen')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Upload Dokumen</h1>

        <a href="{{ route('dokumen-pendaftaran.index') }}"
           class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>

    <div class="section-body">

        @if($peserta->status_pendaftaran === 'approved')
            <div class="alert alert-success">
                Semua dokumen telah disetujui. Upload dinonaktifkan.
            </div>
        @else
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4>Form Upload Dokumen</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('dokumen-pendaftaran.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label>Jenis Dokumen</label>
                            <select name="jenis_dokumen" class="form-control" required>
                                <option value="">Pilih Dokumen</option>

                                @foreach($documents as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ in_array($key, $uploadedTypes) ? 'disabled' : '' }}
                                        {{ $defaultDocument === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                        {{ in_array($key, $uploadedTypes) ? '(Sudah Upload)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>File</label>
                            <input type="file"
                                   name="file"
                                   class="form-control"
                                   required>
                        </div>

                        <button class="btn btn-primary">
                            Upload Dokumen
                        </button>
                    </form>
                </div>
            </div>
        @endif

    </div>
</section>
@endsection
