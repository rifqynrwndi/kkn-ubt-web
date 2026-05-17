@extends('layouts.app')

@section('title', 'Kelola Sesi WAR')

@section('content')

<section class="section">
    <div class="section-header">
        <h1>Kelola Sesi WAR KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.war.index') }}">Kontrol WAR</a></div>
            <div class="breadcrumb-item active">Kelola Sesi</div>
        </div>
    </div>

    <div class="section-body">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    Terjadi kesalahan validasi. Periksa kembali input Anda.
                </div>
            </div>
        @endif

        <div class="row">

            <div class="col-lg-4">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Informasi Sesi</h4>
                        <div class="card-header-action">
                            @if($war->status === 'active')
                                <span class="badge badge-success">Aktif</span>
                            @elseif($war->status === 'closed')
                                <span class="badge badge-success">Selesai</span>
                            @else
                                <span class="badge badge-warning">Terjadwal</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">Nama Sesi</td>
                                <td class="font-weight-bold">{{ $war->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Gelombang</td>
                                <td>{{ $war->gelombang->nama_gelombang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Waktu Mulai</td>
                                <td>{{ $war->start_at?->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Waktu Selesai</td>
                                <td>{{ $war->end_at?->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Peserta</td>
                                <td>
                                    <span class="badge badge-primary">{{ $war->participants_count }} orang</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.war.monitor', $war) }}" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-desktop"></i> Monitor Live
                        </a>

                        @if($war->status === 'scheduled')
                            <button type="button" class="btn btn-warning btn-block mb-2" data-toggle="modal" data-target="#editModal">
                                <i class="fas fa-edit"></i> Edit Sesi
                            </button>
                            <form action="{{ route('admin.war.activate', $war) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block"
                                    onclick="return confirm('Aktifkan sesi ini? Sesi lain yang aktif akan ditutup otomatis.')">
                                    <i class="fas fa-play"></i> Aktifkan Sekarang
                                </button>
                            </form>
                        @endif

                        @if($war->status === 'active')
                            <form action="{{ route('admin.war.stop', $war) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-block"
                                    onclick="return confirm('Hentikan sesi WAR ini sekarang?')">
                                    <i class="fas fa-stop"></i> Hentikan WAR
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.war.reset', $war) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-block btn-sm"
                                onclick="return confirm('PERHATIAN: Reset akan menghapus semua peserta dari sesi ini. Lanjutkan?')">
                                <i class="fas fa-sync-alt"></i> Reset Peserta
                            </button>
                        </form>

                        @if($war->status !== 'active')
                        <form action="{{ route('admin.war.destroy', $war) }}" method="POST" class="mt-2" id="delete-session-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-danger btn-block btn-sm"
                                onclick="if(confirm('Hapus sesi ini secara permanen? Tindakan ini tidak dapat dibatalkan.')) { document.getElementById('delete-session-form').submit(); }">
                                <i class="fas fa-trash"></i> Hapus Sesi
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Jadwal WAR per Fakultas</h4>
                        <div class="card-header-action">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addFacultyModal"
                                {{ $war->status === 'active' ? 'disabled' : '' }}>
                                <i class="fas fa-plus"></i> Tambah Fakultas
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($war->faculties->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Fakultas</th>
                                            <th width="200">Waktu Mulai</th>
                                            <th width="200">Waktu Selesai</th>
                                            <th width="100" class="text-center">Status</th>
                                            <th width="80" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($war->faculties->sortBy('start_at') as $wf)
                                            <tr>
                                                <td class="align-middle">
                                                    <strong>{{ $wf->fakultas->nama_fakultas ?? 'Tidak Diketahui' }}</strong>
                                                </td>
                                                <td class="align-middle">
                                                    @if($wf->start_at)
                                                        <small class="text-muted">{{ $wf->start_at->format('d M Y') }}</small><br>
                                                        <strong>{{ $wf->start_at->format('H:i') }}</strong>
                                                    @else
                                                        <span class="text-muted">Belum diatur</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @if($wf->end_at)
                                                        <small class="text-muted">{{ $wf->end_at->format('d M Y') }}</small><br>
                                                        <strong>{{ $wf->end_at->format('H:i') }}</strong>
                                                    @else
                                                        <span class="text-muted">Belum diatur</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    @if($wf->is_active)
                                                        <span class="badge badge-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-success">{{ $wf->status_jadwal }}</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-center">
                                                    <button type="button" class="btn btn-sm btn-icon btn-primary"
                                                        onclick="editSchedule({{ $wf->id }}, {{ $wf->fakultas_id }}, '{{ $wf->fakultas->nama_fakultas }}', '{{ $wf->start_at?->format('Y-m-d\TH:i') }}', '{{ $wf->end_at?->format('Y-m-d\TH:i') }}')"
                                                        {{ $war->status === 'active' ? 'disabled' : '' }}>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-university fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum ada fakultas yang dikonfigurasi</h5>
                                    <p class="text-muted">Tambahkan fakultas dan atur jadwal WAR untuk memulai.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- Modal Edit Sesi --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.war.update', $war) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Sesi WAR</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Sesi</label>
                    <input type="text" name="name" class="form-control" value="{{ $war->name }}" required>
                </div>
                <div class="form-group">
                    <label>Waktu Mulai</label>
                    <input type="datetime-local" name="start_at" class="form-control"
                           value="{{ $war->start_at?->format('Y-m-d\TH:i') }}" required>
                </div>
                <div class="form-group">
                    <label>Waktu Selesai</label>
                    <input type="datetime-local" name="end_at" class="form-control"
                           value="{{ $war->end_at?->format('Y-m-d\TH:i') }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Fakultas --}}
<div class="modal fade" id="addFacultyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.war.setFacultyQuota', $war) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Fakultas ke WAR</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Pilih Fakultas</label>
                    <p class="text-muted small mb-3">Pilih fakultas yang diizinkan mengikuti sesi WAR ini. Anda bisa memilih lebih dari satu.</p>
                    
                    <div class="row">
                        @foreach($fakultas as $f)
                            <div class="col-md-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="fakultas-{{ $f->id }}" 
                                           name="faculties[]" 
                                           value="{{ $f->id }}"
                                           {{ $war->faculties->contains('fakultas_id', $f->id) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="fakultas-{{ $f->id }}">
                                        {{ $f->nama_fakultas }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Jadwal Fakultas --}}
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.war.setFacultySchedule', $war) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Edit Jadwal: <span id="edit-fakultas-name"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="schedules[0][fakultas_id]" id="edit-fakultas-id">
                
                <div class="form-group">
                    <label>Waktu Mulai</label>
                    <input type="datetime-local" name="schedules[0][start_at]" id="edit-start-at" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Waktu Selesai</label>
                    <input type="datetime-local" name="schedules[0][end_at]" id="edit-end-at" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editSchedule(id, fakultasId, fakultasName, startAt, endAt) {
    $('#edit-fakultas-id').val(fakultasId);
    $('#edit-fakultas-name').text(fakultasName);
    $('#edit-start-at').val(startAt);
    $('#edit-end-at').val(endAt);
    $('#editScheduleModal').modal('show');
}

// Fix: backdrop modal Bootstrap tidak hilang saat ditutup
$(document).on('hidden.bs.modal', '.modal', function () {
    $('.modal-backdrop').remove();
    if (!$('.modal.show').length) {
        $('body').removeClass('modal-open').css('padding-right', '');
    }
});
</script>
@endpush