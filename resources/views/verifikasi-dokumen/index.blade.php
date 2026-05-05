@extends('layouts.app')

@section('title', 'Verifikasi Dokumen KKN')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Verifikasi Dokumen Pendaftaran KKN</h1>
    </div>

    <div class="section-body">

        <div class="card shadow-sm">
            <div class="card-header">
                <h4>Daftar Peserta</h4>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mahasiswa</th>
                                <th>NPM</th>
                                <th>Gelombang</th>
                                <th>Dokumen</th>
                                <th>Status</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($pesertaList as $peserta)
                                <tr>
                                    <td>
                                        <strong>
                                            {{ $peserta->mahasiswa->user->name }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $peserta->mahasiswa->npm }}
                                    </td>

                                    <td>
                                        {{ $peserta->gelombang->nama_gelombang }}
                                    </td>

                                    <td>
                                        <span class="badge badge-info">
                                            {{ $peserta->dokumenPendaftaran->count() }}/4 Dokumen
                                        </span>
                                    </td>

                                    <td>
                                        @if($peserta->status_pendaftaran === 'draft')
                                            <span class="badge badge-secondary">
                                                Draft
                                            </span>
                                        @elseif($peserta->status_pendaftaran === 'pending_verification')
                                            <span class="badge badge-warning">
                                                Pending Verification
                                            </span>
                                        @elseif($peserta->status_pendaftaran === 'pending_documents')
                                            <span class="badge badge-info">
                                                Pending Documents
                                            </span>

                                        @elseif($peserta->status_pendaftaran === 'revision_required')
                                            <span class="badge badge-danger">
                                                Revision Required
                                            </span>
                                        @elseif($peserta->status_pendaftaran === 'approved')
                                            <span class="badge badge-success">
                                                Approved
                                            </span>
                                        @elseif($peserta->status_pendaftaran === 'rejected')
                                            <span class="badge badge-danger">
                                                Rejected
                                            </span>
                                        @elseif($peserta->status_pendaftaran === 'expired')
                                            <span class="badge badge-secondary">
                                                Expired
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('verifikasi-dokumen.show', $peserta->id) }}"
                                           class="btn btn-primary btn-sm btn-block">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="text-center text-muted py-4">
                                        Tidak ada peserta yang perlu diverifikasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($pesertaList, 'links'))
                <div class="card-footer">
                    {{ $pesertaList->links() }}
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
