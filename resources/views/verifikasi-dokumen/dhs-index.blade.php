@extends('layouts.app')

@section('title', 'Verifikasi DHS')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Verifikasi Daftar Hasil Studi (DHS)</h1>
        <a href="{{ route('verifikasi-dokumen.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Verifikasi Dokumen
        </a>
    </div>

    <div class="section-body">

        {{-- Status Filter --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('verifikasi-dokumen.dhs.index') }}" class="row align-items-end">
                    <div class="col-md-10">
                        <label class="form-label font-weight-bold">Filter Status</label>
                        <select name="status" class="form-control" onchange="this.form.submit()">
                            <option value="">Pending (Menunggu Review)</option>
                            <option value="pending" {{ $statusFilter == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ $statusFilter == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ $statusFilter == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- DHS List --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h4 class="mb-0">Daftar DHS</h4>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Mahasiswa</th>
                                <th>NPM</th>
                                <th>Fakultas / Prodi</th>
                                <th>Status DHS</th>
                                <th>Di-upload</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($mahasiswaList as $mhs)
                                <tr>
                                    <td>
                                        <strong>{{ $mhs->user->name }}</strong>
                                    </td>
                                    <td>{{ $mhs->npm }}</td>
                                    <td>
                                        <small>{{ $mhs->fakultas }} / {{ $mhs->prodi }}</small>
                                    </td>
                                    <td>
                                        @switch($mhs->dhs_status)
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
                                    <td>
                                        <small>{{ $mhs->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('verifikasi-dokumen.dhs.show', $mhs->user_id) }}"
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye mr-1"></i> Review
                                        </a>

                                        @if($mhs->dhs_status === 'pending')
                                            <form action="{{ route('verifikasi-dokumen.dhs.verify', $mhs->user_id) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        @if($statusFilter === 'verified')
                                            Belum ada DHS yang terverifikasi.
                                        @elseif($statusFilter === 'rejected')
                                            Belum ada DHS yang ditolak.
                                        @else
                                            Tidak ada DHS yang menunggu review.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($mahasiswaList, 'links'))
                <div class="card-footer">
                    {{ $mahasiswaList->links() }}
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
