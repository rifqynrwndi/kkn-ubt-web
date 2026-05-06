@extends('layouts.app')

@section('title', 'Dokumen Pendaftaran')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between">
        <h1>Dokumen Pendaftaran KKN</h1>

        <a href="{{ route('dokumen-pendaftaran.create') }}" class="btn btn-primary">
            Upload Dokumen
        </a>
    </div>

    <div class="section-body">

        {{-- QUICK STATUS CARD --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body d-flex justify-content-between">

                        <div>
                            <h6 class="text-muted mb-1">Status Pendaftaran</h6>
                            <h4 class="mb-0">
                                {{ ucfirst(str_replace('_',' ', $peserta->status_pendaftaran)) }}
                            </h4>
                        </div>

                        <div class="text-primary" style="font-size: 2rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>

                    </div>
                </div>
            </div>
        </div>

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
                                    <strong>{{ $label }}</strong>

                                    @if(!$doc)
                                        <span class="badge badge-secondary">Belum Upload</span>
                                    @elseif($doc->status_verifikasi === 'verified')
                                        <span class="badge badge-success">Verified</span>
                                    @elseif($doc->status_verifikasi === 'revision_required')
                                        <span class="badge badge-warning">Revisi</span>
                                    @elseif($doc->status_verifikasi === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-info">Pending</span>
                                    @endif
                                </div>

                                @if($doc)
                                    <small class="text-muted">
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
                                <td>{{ $item->jenis_dokumen_label }}</td>

                                <td>
                                    @if($item->status_verifikasi === 'verified')
                                        <span class="badge badge-success">Verified</span>
                                    @elseif($item->status_verifikasi === 'revision_required')
                                        <span class="badge badge-warning">Revision</span>
                                    @elseif($item->status_verifikasi === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                    @else
                                        <span class="badge badge-secondary">Pending</span>
                                    @endif
                                </td>

                                <td>{{ $item->catatan_revisi ?? '-' }}</td>

                                <td>{{ $item->verifier?->name ?? '-' }}</td>

                                <td>{{ $item->verified_at?->format('d M Y H:i') ?? '-' }}</td>

                                <td>
                                    <a href="{{ route('dokumen-pendaftaran.show', $item->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       target="_blank">
                                        Lihat
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Belum ada dokumen
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>

            </div>
        </div>

    </div>
</section>
@endsection
