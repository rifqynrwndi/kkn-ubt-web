@extends('layouts.app')

@section('title', 'Verifikasi Dokumen Peserta')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Verifikasi Dokumen Peserta</h1>

        <a href="{{ route('verifikasi-dokumen.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali
        </a>
    </div>

    <div class="section-body">

        {{-- INFO PESERTA --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Nama Mahasiswa</small>
                        <div class="font-weight-bold">
                            {{ $peserta->mahasiswa->user->name }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">NPM</small>
                        <div class="font-weight-bold">
                            {{ $peserta->mahasiswa->npm }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">Gelombang</small>
                        <div class="font-weight-bold">
                            {{ $peserta->gelombang->nama_gelombang }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SUMMARY --}}
        @php
            $verifiedCount = $peserta->dokumenPendaftaran->where('status_verifikasi', 'verified')->count();
            $rejectedCount = $peserta->dokumenPendaftaran->where('status_verifikasi', 'rejected')->count();
            $pendingCount = $peserta->dokumenPendaftaran->where('status_verifikasi', 'pending')->count();
        @endphp

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="alert alert-success mb-0">Terverifikasi: {{ $verifiedCount }}</div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-danger mb-0">Ditolak: {{ $rejectedCount }}</div>
            </div>
            <div class="col-md-4">
                <div class="alert alert-secondary mb-0">Menunggu: {{ $pendingCount }}</div>
            </div>
        </div>

        <form action="{{ route('verifikasi-dokumen.bulk-update', $peserta->id) }}"
              method="POST"
              id="bulkVerificationForm">
            @csrf
            @method('PUT')

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-2 mb-md-0">Daftar Dokumen</h4>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button"
                                class="btn btn-success btn-sm mr-2 bulk-set-status"
                                data-status="verified">
                            Setujui Semua
                        </button>

                        <button type="button"
                                class="btn btn-danger btn-sm bulk-set-status"
                                data-status="rejected">
                            Tolak Semua
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Dokumen</th>
                                    <th>Status Saat Ini</th>
                                    <th>File</th>
                                    <th width="220">Ubah Status</th>
                                    <th>Catatan Revisi</th>
                                    <th width="70">Hapus</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($peserta->dokumenPendaftaran as $dokumen)
                                    @php
                                        $rowClass = match($dokumen->status_verifikasi) {
                                            'verified' => 'table',
                                            'rejected' => 'table',
                                            default => '',
                                        };

                                        $statusLabel = match($dokumen->status_verifikasi) {
                                            'verified' => 'Terverifikasi',
                                            'rejected' => 'Ditolak',
                                            default => 'Menunggu',
                                        };
                                    @endphp

                                    <tr class="{{ $rowClass }}">
                                        <td>
                                            <strong>{{ $dokumen->jenis_dokumen_label }}</strong>
                                        </td>

                                        <td>
                                            <span class="badge badge-pill
                                                @if($dokumen->status_verifikasi === 'verified') badge-success
                                                @elseif($dokumen->status_verifikasi === 'rejected') badge-danger
                                                @else badge-info
                                                @endif">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>

                                        <td>
                                            @if($dokumen->file)
                                                <a href="{{ route('verifikasi-dokumen.dokumen.file', $dokumen->id) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                    Lihat
                                                </a>
                                            @else
                                                <span class="badge badge-secondary">
                                                    File tidak tersedia
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <select name="documents[{{ $dokumen->id }}][status_verifikasi]"
                                                    class="form-control status-select">
                                                <option value="verified"
                                                    {{ $dokumen->status_verifikasi === 'verified' ? 'selected' : '' }}>
                                                    Terverifikasi
                                                </option>

                                                <option value="rejected"
                                                    {{ $dokumen->status_verifikasi === 'rejected' ? 'selected' : '' }}>
                                                    Ditolak
                                                </option>
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text"
                                                   name="documents[{{ $dokumen->id }}][catatan_revisi]"
                                                   class="form-control"
                                                   value="{{ $dokumen->catatan_revisi }}"
                                                   placeholder="Catatan revisi / alasan">
                                        </td>

                                        <td>
                                            <button type="submit"
                                                    form="delete-doc-{{ $dokumen->id }}"
                                                    class="btn btn-sm btn-danger"
                                                    title="Hapus dokumen"
                                                    onclick="return confirm('Hapus dokumen ini? File akan dihapus permanen dan mahasiswa harus mengunggah ulang.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Belum ada dokumen yang diunggah.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer sticky-bottom border-top">
                    <button class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-save mr-1"></i>
                        Simpan Semua Perubahan
                    </button>
                </div>
            </div>
        </form>

        @foreach($peserta->dokumenPendaftaran as $dokumen)
            <form id="delete-doc-{{ $dokumen->id }}"
                  action="{{ route('verifikasi-dokumen.dokumen.destroy', $dokumen->id) }}"
                  method="POST"
                  class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    </div>
</section>
@endsection


@push('scripts')
<script>
    let formDirty = false;

    document.querySelectorAll('.status-select, input[name*="catatan_revisi"]').forEach(el => {
        el.addEventListener('change', () => formDirty = true);
    });

    window.addEventListener('beforeunload', function (e) {
        if (formDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    document.querySelectorAll('.bulk-set-status').forEach(button => {
        button.addEventListener('click', function () {
            const status = this.dataset.status;

            document.querySelectorAll('.status-select').forEach(select => {
                select.value = status;
            });

            formDirty = true;
        });
    });

    document.getElementById('bulkVerificationForm').addEventListener('submit', function () {
        formDirty = false;
    });
</script>
@endpush
