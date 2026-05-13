@extends('layouts.app')

@section('title', 'Dokumen Pendaftaran')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Dokumen Pendaftaran KKN</h1>

        @if($peserta)
            <a href="{{ route('dokumen-pendaftaran.create') }}"
               class="btn btn-primary">
                <i class="fas fa-upload"></i>
                Upload Dokumen
            </a>
        @endif
    </div>

    <div class="section-body">

        {{-- SUCCESS --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- ERROR --}}
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        {{-- BELUM DAFTAR --}}
        @if(!$peserta)

            <div class="card shadow-sm">
                <div class="card-body text-center py-5">

                    <div class="mb-4">
                        <i class="fas fa-file-alt text-muted"
                           style="font-size: 5rem;"></i>
                    </div>

                    <h4 class="mb-3">
                        Anda Belum Terdaftar KKN
                    </h4>

                    <p class="text-muted mb-4">
                        Silakan daftar program KKN terlebih dahulu
                        sebelum mengupload dokumen pendaftaran.
                    </p>

                    <a href="{{ route('pendaftaran-kkn.index') }}"
                       class="btn btn-primary px-4">
                        <i class="fas fa-paper-plane"></i>
                        Daftar KKN
                    </a>

                </div>
            </div>

        @else

            {{-- QUICK STATUS CARD --}}
            <div class="row mb-4">

                <div class="col-12">

                    <div class="card shadow-sm">

                        <div class="card-body d-flex justify-content-between align-items-center">

                            <div>
                                <h6 class="text-muted mb-1">
                                    Status Pendaftaran
                                </h6>

                                <h4 class="mb-0">

                                    @if($peserta->status_pendaftaran === 'draft')
                                        Draft
                                    @elseif($peserta->status_pendaftaran === 'pending_documents')
                                        Pending Documents
                                    @elseif($peserta->status_pendaftaran === 'revision')
                                        Revision
                                    @elseif($peserta->status_pendaftaran === 'verified')
                                        Verified
                                    @elseif($peserta->status_pendaftaran === 'rejected')
                                        Rejected
                                    @else
                                        {{ ucfirst(str_replace('_',' ', $peserta->status_pendaftaran)) }}
                                    @endif

                                </h4>
                            </div>

                            <div class="text-primary"
                                 style="font-size: 2rem;">
                                <i class="fas fa-file-alt"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- PROGRESS DOKUMEN --}}
            <div class="card shadow-sm mb-4">

                <div class="card-header">
                    <h4>Progress Dokumen</h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        @foreach($requiredDocuments as $key => $label)

                            @php
                                $doc = $uploadedDocuments[$key] ?? null;
                            @endphp

                            <div class="col-md-3 mb-3">

                                <div class="border rounded p-3 h-100">

                                    <div class="d-flex justify-content-between align-items-center mb-2">

                                        <strong>
                                            {{ $label }}
                                        </strong>

                                        @if(!$doc)

                                            <span class="badge badge-outline-info">
                                                Belum Upload
                                            </span>

                                        @elseif($doc->status_verifikasi === 'verified')

                                            <span class="badge badge-success">
                                                Verified
                                            </span>

                                        @elseif($doc->status_verifikasi === 'revision_required')

                                            <span class="badge badge-warning">
                                                Revisi
                                            </span>

                                        @elseif($doc->status_verifikasi === 'rejected')

                                            <span class="badge badge-danger">
                                                Rejected
                                            </span>

                                        @else

                                            <span class="badge badge-info">
                                                Pending
                                            </span>

                                        @endif

                                    </div>

                                    @if($doc)

                                        <small class="text-success">
                                            Sudah diupload
                                        </small>

                                    @else

                                        <small class="text-danger">
                                            Belum diupload
                                        </small>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>


            {{-- TABLE --}}
            <div class="card">

                <div class="card-header">
                    <h4>Detail Dokumen</h4>
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-hover">

                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Verifier</th>
                                <th>Waktu</th>
                                <th>File</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($dokumen as $item)

                                <tr>

                                    <td>
                                        {{ $item->jenis_dokumen_label }}
                                    </td>

                                    <td>

                                        @if($item->status_verifikasi === 'verified')

                                            <span class="badge badge-success">
                                                Verified
                                            </span>

                                        @elseif($item->status_verifikasi === 'revision_required')

                                            <span class="badge badge-warning">
                                                Revision
                                            </span>

                                        @elseif($item->status_verifikasi === 'rejected')

                                            <span class="badge badge-danger">
                                                Rejected
                                            </span>

                                        @else

                                            <span class="badge badge-info">
                                                Pending
                                            </span>

                                        @endif

                                    </td>

                                    <td>
                                        {{ $item->catatan_revisi ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->verifier?->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->verified_at?->format('d M Y H:i') ?? '-' }}
                                    </td>

                                    <td>

                                        <a href="{{ route('dokumen-pendaftaran.show', $item->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           target="_blank">

                                            <i class="fas fa-eye"></i>
                                            Lihat

                                        </a>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6"
                                        class="text-center text-muted py-4">

                                        Belum ada dokumen

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        @endif

    </div>

</section>
@endsection
