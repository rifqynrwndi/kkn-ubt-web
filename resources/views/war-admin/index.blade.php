@extends('layouts.app')

@section('title', 'War Control')

@section('content')

<section class="section">
    <div class="section-header">
        <h1>War Control Dashboard</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">War Control</div>
        </div>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    Terjadi kesalahan input.
                </div>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-12 text-right">
                <button class="btn btn-primary" data-toggle="modal" data-target="#createWarModal">
                    <i class="fas fa-plus"></i> Create War Session
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>All War Sessions</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Gelombang</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th>Participants</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wars as $war)
                                <tr>
                                    <td><strong>{{ $war->name }}</strong></td>
                                    <td>{{ $war->gelombang->nama_gelombang ?? '-' }}</td>
                                    <td>
                                        <div class="text-small text-muted">Mulai: {{ $war->start_at?->format('d M Y, H:i') }}</div>
                                        <div class="text-small text-muted">Selesai: {{ $war->end_at?->format('d M Y, H:i') }}</div>
                                    </td>
                                    <td>
                                        @if($war->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @elseif($war->status === 'closed')
                                            <span class="badge badge-secondary">Closed</span>
                                        @else
                                            <span class="badge badge-warning">Scheduled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $war->participants_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.war.show', $war) }}" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Manage Session">
                                            <i class="fas fa-cog"></i>
                                        </a>
                                        <a href="{{ route('admin.war.monitor', $war) }}" class="btn btn-sm btn-info" data-toggle="tooltip" title="Live Monitor">
                                            <i class="fas fa-desktop"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No war sessions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Create War -->
<div class="modal fade" id="createWarModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.war.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Create War Session</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: War KKN Batch 1">
                </div>
                <div class="form-group">
                    <label>Gelombang KKN</label>
                    <select name="gelombang_id" class="form-control select2" required>
                        <option value="">-- Pilih Gelombang --</option>
                        @foreach($gelombangs as $gel)
                            <option value="{{ $gel->id }}">{{ $gel->nama_gelombang }} ({{ $gel->tahun }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Start At</label>
                    <input type="datetime-local" name="start_at" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>End At</label>
                    <input type="datetime-local" name="end_at" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $('[data-toggle="tooltip"]').tooltip();
</script>
@endpush
