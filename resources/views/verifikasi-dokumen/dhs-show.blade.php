@extends('layouts.app')

@section('title', 'Review DHS Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Review DHS — {{ $mahasiswa->user->name }}</h1>
        <a href="{{ url('/mahasiswa/' . $mahasiswa->user_id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="section-body">
        <div class="row">

            {{-- Info Mahasiswa --}}
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data Mahasiswa</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted" width="120">Nama</td>
                                <td class="font-weight-bold">{{ $mahasiswa->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">NPM</td>
                                <td>{{ $mahasiswa->npm }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fakultas</td>
                                <td>{{ $mahasiswa->fakultas }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Prodi</td>
                                <td>{{ $mahasiswa->prodi }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status DHS</td>
                                <td>
                                    @switch($mahasiswa->dhs_status)
                                        @case('pending')
                                            <span class="badge badge-warning">Menunggu Review</span>
                                            @break
                                        @case('verified')
                                            <span class="badge badge-success">Terverifikasi</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge badge-danger">Ditolak</span>
                                            @break
                                    @endswitch
                                </td>
                            </tr>
                            @if($mahasiswa->dhs_verified_by)
                            <tr>
                                <td class="text-muted">Diverifikasi oleh</td>
                                <td>{{ $mahasiswa->dhsVerifier->name ?? '-' }}</td>
                            </tr>
                            @endif
                            @if($mahasiswa->dhs_verified_at)
                            <tr>
                                <td class="text-muted">Waktu Verifikasi</td>
                                <td>{{ $mahasiswa->dhs_verified_at->format('d M Y H:i') }}</td>
                            </tr>
                            @endif
                            @if($mahasiswa->dhs_catatan)
                            <tr>
                                <td class="text-muted">Catatan</td>
                                <td class="text-danger">{{ $mahasiswa->dhs_catatan }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Preview DHS --}}
            <div class="col-lg-8">
                @if($mahasiswa->dhs_path)
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Preview DHS</h5>
                        <a href="{{ Storage::url($mahasiswa->dhs_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-external-link-alt mr-1"></i> Buka di Tab Baru
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <iframe src="{{ Storage::url($mahasiswa->dhs_path) }}"
                                style="width:100%;height:600px;border:none;"
                                title="Preview DHS"></iframe>
                    </div>
                </div>
                @else
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                        <h5>File DHS belum diupload</h5>
                    </div>
                </div>
                @endif

                {{-- Aksi Verifikasi --}}
                @if($mahasiswa->dhs_status === 'pending')
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Aksi Verifikasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Approve --}}
                            <div class="col-md-6">
                                <form action="{{ route('verifikasi-dokumen.dhs.verify', $mahasiswa->user_id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success btn-lg btn-block">
                                        <i class="fas fa-check mr-1"></i> Verifikasi DHS
                                    </button>
                                    <small class="text-muted d-block mt-1">DHS sesuai, mahasiswa lolos verifikasi identitas.</small>
                                </form>
                            </div>

                            {{-- Reject --}}
                            <div class="col-md-6">
                                <form action="{{ route('verifikasi-dokumen.dhs.reject', $mahasiswa->user_id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group">
                                        <label class="font-weight-bold">Alasan Penolakan <span class="text-danger">*</span></label>
                                        <textarea name="catatan" class="form-control" rows="3"
                                                  placeholder="Contoh: DHS tidak terbaca, foto tidak sesuai, NPM salah..."
                                                  required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-lg btn-block">
                                        <i class="fas fa-times mr-1"></i> Tolak DHS
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</section>
@endsection
