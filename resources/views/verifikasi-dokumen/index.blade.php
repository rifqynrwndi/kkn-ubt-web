@extends('layouts.app')

@section('title', 'Verifikasi Dokumen KKN')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Verifikasi Dokumen Pendaftaran KKN</h1>
    </div>

    <div class="section-body">

        <form action="{{ route('verifikasi-dokumen.bulk-approve') }}" method="POST">
            @csrf

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h4 class="mb-0">Daftar Peserta</h4>

                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check mr-1"></i>
                        Approve Selected
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="checkAll">
                                    </th>
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
                                            @php
                                                $canBulkApprove =
                                                    $peserta->dokumenPendaftaran->count() === count(\App\Models\DokumenPendaftaran::REQUIRED_DOCUMENTS)
                                                    && $peserta->status_pendaftaran !== 'approved';
                                            @endphp

                                            @if($canBulkApprove)
                                                <input type="checkbox"
                                                    name="peserta_ids[]"
                                                    value="{{ $peserta->id }}">
                                            @endif
                                        </td>

                                        <td>
                                            <strong>{{ $peserta->mahasiswa->user->name }}</strong>
                                        </td>

                                        <td>{{ $peserta->mahasiswa->npm }}</td>

                                        <td>{{ $peserta->gelombang->nama_gelombang }}</td>

                                        <td>
                                            <span class="badge badge-info">
                                                {{ $peserta->dokumenPendaftaran->count() }}/5 Dokumen
                                            </span>
                                        </td>

                                        <td>
                                            @switch($peserta->status_pendaftaran)
                                                @case('draft')
                                                    <span class="badge">Draft</span>
                                                    @break

                                                @case('pending_documents')
                                                    <span class="badge badge-info">Pending Documents</span>
                                                    @break

                                                @case('pending_verification')
                                                    <span class="badge badge-warning">Pending Verification</span>
                                                    @break

                                                @case('revision')
                                                    <span class="badge badge-danger">Revision Required</span>
                                                    @break

                                                @case('approved')
                                                    <span class="badge badge-success">Approved</span>
                                                    @break

                                                @case('rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                    @break

                                                @case('expired')
                                                    <span class="badge badge-secondary">Expired</span>
                                                    @break
                                            @endswitch
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
                                        <td colspan="7"
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
        </form>

    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('checkAll')?.addEventListener('change', function () {
    document.querySelectorAll('input[name="peserta_ids[]"]').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>
@endpush
