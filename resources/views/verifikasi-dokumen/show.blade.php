@extends('layouts.app')

@section('title', 'Review Dokumen Peserta')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Review Dokumen Peserta</h1>

        <a href="{{ route('verifikasi-dokumen.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali
        </a>
    </div>

    <div class="section-body">

        {{-- INFORMASI PESERTA --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Nama Mahasiswa</small>
                        <strong>{{ $peserta->mahasiswa->user->name }}</strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">NPM</small>
                        <strong>{{ $peserta->mahasiswa->npm }}</strong>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Gelombang</small>
                        <strong>{{ $peserta->gelombang->nama_gelombang }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- LIST DOKUMEN --}}
        @foreach($peserta->dokumenPendaftaran as $dokumen)
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        {{ $dokumen->jenis_dokumen_label }}
                    </h4>

                    @if($dokumen->status_verifikasi === 'verified')
                        <span class="badge badge-success">Terverifikasi</span>
                    @elseif($dokumen->status_verifikasi === 'revision_required')
                        <span class="badge badge-warning">Revisi</span>
                    @elseif($dokumen->status_verifikasi === 'rejected')
                        <span class="badge badge-danger">Ditolak</span>
                    @else
                        <span class="badge badge-info">Pending</span>
                    @endif
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <a href="{{ route('dokumen-pendaftaran.show', $dokumen->id) }}"
                           target="_blank"
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye mr-1"></i>
                            Lihat Dokumen
                        </a>
                    </div>

                    <form action="{{ route('verifikasi-dokumen.update', $dokumen->id) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status Verifikasi</label>
                                    <select name="status_verifikasi"
                                            class="form-control"
                                            required>
                                        <option value="verified"
                                            {{ $dokumen->status_verifikasi === 'verified' ? 'selected' : '' }}>
                                            Verified
                                        </option>

                                        <option value="revision_required"
                                            {{ $dokumen->status_verifikasi === 'revision_required' ? 'selected' : '' }}>
                                            Revision Required
                                        </option>

                                        <option value="rejected"
                                            {{ $dokumen->status_verifikasi === 'rejected' ? 'selected' : '' }}>
                                            Rejected
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>Catatan Admin / Revisi</label>
                                    <input type="text"
                                           name="catatan_revisi"
                                           class="form-control"
                                           value="{{ $dokumen->catatan_revisi }}"
                                           placeholder="Tambahkan catatan bila perlu">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="d-block invisible">Action</label>
                                    <button class="btn btn-primary btn-block">
                                        Simpan
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        @endforeach

    </div>
</section>
@endsection
