@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Detail Biodata Mahasiswa</h1>
    </div>

    <div class="section-body">
        <div class="card">

            <div class="card-header">
                <h4>{{ $mahasiswa->name }}</h4>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3 text-center mb-4">
                        <img
                            src="{{ $mahasiswa->mahasiswa?->foto
                                ? storage_url($mahasiswa->mahasiswa->foto)
                                : asset('img/avatar/avatar-1.png') }}"
                            class="rounded-circle shadow"
                            width="180"
                            height="180"
                            style="object-fit: cover;"
                        >
                    </div>

                    <div class="col-md-9">
                        <table class="table table-borderless">

                            <tr>
                                <th width="30%">Nama Lengkap</th>
                                <td>{{ $mahasiswa->name }}</td>
                            </tr>

                            <tr>
                                <th>Email</th>
                                <td>{{ $mahasiswa->email }}</td>
                            </tr>

                            <tr>
                                <th>Status Email</th>
                                <td>
                                    @if($mahasiswa->email_verified_at)
                                        <span class="badge badge-success">
                                            Verified
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            Not Verified
                                        </span>
                                        <form action="{{ route('mahasiswa.verify-email', $mahasiswa->id) }}" method="POST" class="d-inline ml-2">
                                            @csrf
                                            <button class="btn btn-outline-success btn-sm" onclick="return confirm('Verifikasi email {{ $mahasiswa->email }}?')">
                                                <i class="fas fa-check"></i> Verifikasi
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>NPM</th>
                                <td>{{ $mahasiswa->mahasiswa?->npm ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Jenis Kelamin</th>
                                <td>
                                    {{ $mahasiswa->mahasiswa?->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Program Studi</th>
                                <td>{{ $mahasiswa->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>No HP</th>
                                <td>{{ $mahasiswa->mahasiswa?->no_hp ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Nama Orang Tua / Wali</th>
                                <td>{{ $mahasiswa->mahasiswa?->nama_ortu ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>No HP Orang Tua</th>
                                <td>{{ $mahasiswa->mahasiswa?->no_hp_ortu ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Alamat Orang Tua</th>
                                <td>{{ $mahasiswa->mahasiswa?->alamat_ortu ?? '-' }}</td>
                            </tr>

                            <tr>
                                <th>Status Biodata</th>
                                <td>
                                    @if($mahasiswa->mahasiswa?->is_biodata_complete && $mahasiswa->mahasiswa?->dhs_status === 'verified')
                                        <span class="badge badge-success">
                                            Lengkap
                                        </span>
                                    @elseif($mahasiswa->mahasiswa?->is_biodata_complete)
                                        <span class="badge badge-info">
                                            Biodata Lengkap, DHS Menunggu
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            Belum Lengkap
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        </table>
                    </div>

                </div>

            </div>

        </div>

        {{-- DHS SECTION --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Daftar Hasil Studi (DHS)</h4>
                @if($mahasiswa->mahasiswa?->dhs_status === 'verified')
                    <span class="badge badge-success" style="font-size:13px;padding:6px 12px;"><i class="fas fa-check-circle mr-1"></i> Terverifikasi</span>
                @elseif($mahasiswa->mahasiswa?->dhs_status === 'rejected')
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 12px;"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                @elseif($mahasiswa->mahasiswa?->dhs_status === 'pending' && $mahasiswa->mahasiswa?->dhs_path)
                    <span class="badge badge-warning" style="font-size:13px;padding:6px 12px;"><i class="fas fa-clock mr-1"></i> Menunggu Verifikasi</span>
                @else
                    <span class="badge badge-secondary" style="font-size:13px;padding:6px 12px;"><i class="fas fa-upload mr-1"></i> Belum Diunggah</span>
                @endif
            </div>
            <div class="card-body">
                @if($mahasiswa->mahasiswa?->dhs_path)
                    {{-- Rejection Note --}}
                    @if($mahasiswa->mahasiswa?->dhs_status === 'rejected' && $mahasiswa->mahasiswa?->dhs_catatan)
                        <div class="alert alert-danger">
                            <strong>Catatan Penolakan:</strong> {{ $mahasiswa->mahasiswa->dhs_catatan }}
                        </div>
                    @endif

                    {{-- PDF Preview --}}
                    <div class="mb-3">
                        <label class="text-muted small">Dokumen DHS:</label>
                        <div class="border rounded p-2 d-flex align-items-center dhs-file-preview-box">
                            <i class="fas fa-file-pdf text-danger" style="font-size:24px;margin-right:12px;"></i>
                            <div>
                                <strong>{{ basename($mahasiswa->mahasiswa->dhs_path) }}</strong>
                            </div>
                        </div>
                        <div class="mt-2">
                            <a href="{{ storage_url($mahasiswa->mahasiswa->dhs_path) }}"
                               target="_blank"
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye mr-1"></i> Lihat DHS
                            </a>
                            <a href="{{ storage_url($mahasiswa->mahasiswa->dhs_path) }}"
                               download
                               class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-download mr-1"></i> Unduh
                            </a>
                        </div>
                    </div>

                    {{-- Verify / Reject --}}
                    @if($mahasiswa->mahasiswa?->dhs_status !== 'verified')
                        <div class="d-flex gap-2 mt-3">
                            <form action="{{ route('verifikasi-dokumen.dhs.verify', $mahasiswa->mahasiswa->user_id) }}" method="POST" class="d-inline">
                                @csrf @method('PUT')
                                <button class="btn btn-success" onclick="return confirm('Verifikasi DHS ini?')">
                                    <i class="fas fa-check mr-1"></i> Verifikasi
                                </button>
                            </form>
                            <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times mr-1"></i> Tolak
                            </button>
                        </div>

                        {{-- Reject Modal --}}
                        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <form action="{{ route('verifikasi-dokumen.dhs.reject', $mahasiswa->mahasiswa->user_id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tolak DHS</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Catatan Penolakan <span class="text-danger">*</span></label>
                                                <textarea name="dhs_catatan" class="form-control" rows="3" required
                                                          placeholder="Masukkan alasan penolakan..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak DHS</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($mahasiswa->mahasiswa?->dhs_verified_at)
                        <div class="text-muted small mt-3">
                            <i class="fas fa-clock mr-1"></i>
                            Diverifikasi pada {{ $mahasiswa->mahasiswa->dhs_verified_at->translatedFormat('d M Y H:i') }}
                        </div>
                    @endif
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-upload fa-3x mb-3" style="opacity:0.3;"></i>
                        <p>Mahasiswa belum mengunggah DHS.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>
@endsection
