@extends('layouts.app')

@section('title', 'Plotting Kelompok')

@section('content')

<section class="section">
    <div class="section-header">
        <h1>Plotting Kelompok KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Plotting Kelompok</div>
        </div>
    </div>

    <div class="section-body">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    <i class="fas fa-exclamation-circle mr-2"></i> Terjadi kesalahan pada input. Harap periksa kembali.
                </div>
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h6 class="text-muted mb-0">
                    <i class="fas fa-list mr-1"></i>
                    Menampilkan {{ $wars->count() }} sesi Plotting Kelompok
                </h6>
                <a href="{{ route('admin.war.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Buat Sesi Plotting Baru
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-layer-group mr-2 text-primary"></i> Daftar Semua Sesi Plotting</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama Sesi Plotting</th>
                                <th>Gelombang KKN</th>
                                <th>Jadwal Pelaksanaan</th>
                                <th>Status</th>
                                <th class="text-center">Peserta</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wars as $war)
                                <tr>
                                    <td>
                                        <strong>{{ $war->name }}</strong>
                                    </td>
                                    <td>{{ $war->gelombang->nama_gelombang ?? '-' }}</td>
                                    <td>
                                        <div class="text-small">
                                            <i class="fas fa-play-circle text-success mr-1"></i>
                                            <span class="text-muted">Mulai:</span> {{ $war->start_at?->format('d M Y, H:i') }}
                                        </div>
                                        <div class="text-small mt-1">
                                            <i class="fas fa-stop-circle text-danger mr-1"></i>
                                            <span class="text-muted">Selesai:</span> {{ $war->end_at?->format('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($war->status === 'active')
                                            <span class="badge badge-success" style="border-radius:20px;padding:5px 12px;">
                                                <i class="fas fa-circle fa-xs mr-1"></i> Aktif
                                            </span>
                                        @elseif($war->status === 'closed')
                                            <span class="badge badge-success" style="border-radius:20px;padding:5px 12px;">
                                                <i class="fas fa-lock fa-xs mr-1"></i> Selesai
                                            </span>
                                        @else
                                            <span class="badge badge-warning" style="border-radius:20px;padding:5px 12px;">
                                                <i class="fas fa-clock fa-xs mr-1"></i> Terjadwal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info" style="border-radius:20px;padding:5px 12px;font-size:13px;">
                                            {{ $war->participants_count }} peserta
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.war.show', $war) }}"
                                           class="btn btn-sm btn-primary"
                                           data-toggle="tooltip" title="Kelola Sesi Plotting">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <a href="{{ route('admin.war.monitor', $war) }}"
                                           class="btn btn-sm btn-info"
                                           data-toggle="tooltip" title="Monitor Plotting Live">
                                            <i class="fas fa-desktop"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Belum ada sesi Plotting yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>



@endsection

@push('scripts')
<script>
    $('[data-toggle="tooltip"]').tooltip();
    if(jQuery().select2) {
        $(".select2").select2();
    }


</script>
@endpush
