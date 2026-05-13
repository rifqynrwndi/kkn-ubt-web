@extends('layouts.app')

@section('title', 'Manage War Session')

@section('content')

<section class="section">
    <div class="section-header">
        <h1>Manage War Session</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.war.index') }}">War Control</a></div>
            <div class="breadcrumb-item active">Manage Session</div>
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
                    Terjadi kesalahan input atau validasi.
                </div>
            </div>
        @endif

        <div class="row">
            {{-- SESSION DETAILS CARD --}}
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Session Details</h4>
                        @if($war->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @elseif($war->status === 'closed')
                            <span class="badge badge-secondary">Closed</span>
                        @else
                            <span class="badge badge-warning">Scheduled</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="35%">Name</th>
                                <td>{{ $war->name }}</td>
                            </tr>
                            <tr>
                                <th>Gelombang</th>
                                <td>{{ $war->gelombang->nama_gelombang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Start Time</th>
                                <td>{{ $war->start_at?->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <th>End Time</th>
                                <td>{{ $war->end_at?->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Participants</th>
                                <td>{{ $war->participants_count }} joined</td>
                            </tr>
                        </table>

                        <hr>
                        
                        <div class="d-flex justify-content-between flex-wrap" style="gap: 10px;">
                            <a href="{{ route('admin.war.monitor', $war) }}" class="btn btn-info flex-grow-1">
                                <i class="fas fa-desktop"></i> Live Monitor
                            </a>
                            
                            @if($war->status === 'scheduled')
                                <button type="button" class="btn btn-warning flex-grow-1" data-toggle="modal" data-target="#editModal">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('admin.war.activate', $war) }}" method="POST" class="flex-grow-1 d-flex">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100" onclick="return confirm('Activate this session? Other active sessions will be closed.')">
                                        <i class="fas fa-play"></i> Activate
                                    </button>
                                </form>
                            @endif

                            @if($war->status === 'active')
                                <form action="{{ route('admin.war.stop', $war) }}" method="POST" class="flex-grow-1 d-flex">
                                    @csrf
                                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Stop this WAR session?')">
                                        <i class="fas fa-stop"></i> Stop WAR
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.war.reset', $war) }}" method="POST" class="w-100 mt-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-block" onclick="return confirm('DANGER: This will remove ALL participants from this session and empty the groups. Proceed?')">
                                    <i class="fas fa-sync-alt"></i> Reset Participants
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FACULTY CONFIGURATION --}}
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Faculty Configuration</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.war.setFacultyQuota', $war) }}" method="POST" class="mb-4">
                            @csrf
                            <div class="form-group">
                                <label>Select Faculties for WAR</label>
                                <p class="text-muted text-small">Choose the faculties that are allowed to participate in this session.</p>
                                <select name="faculties[]" class="form-control select2" multiple required>
                                    @foreach($fakultas as $f)
                                        <option value="{{ $f->id }}" 
                                            {{ $war->faculties->contains('fakultas_id', $f->id) ? 'selected' : '' }}>
                                            {{ $f->nama_fakultas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" {{ $war->status === 'active' ? 'disabled' : '' }}>
                                Save Faculties
                            </button>
                        </form>

                        @if($war->faculties->count() > 0)
                            <hr>
                            <h5>Faculty Schedule</h5>
                            <form action="{{ route('admin.war.setFacultySchedule', $war) }}" method="POST">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Faculty</th>
                                                <th>Quota</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($war->faculties as $index => $wf)
                                                <tr>
                                                    <td class="align-middle">
                                                        {{ $wf->fakultas->nama_fakultas ?? 'Unknown' }}
                                                        <input type="hidden" name="schedules[{{ $index }}][fakultas_id]" value="{{ $wf->fakultas_id }}">
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="badge badge-primary">{{ $wf->quota }}</span>
                                                    </td>
                                                    <td>
                                                        <input type="datetime-local" class="form-control form-control-sm" 
                                                               name="schedules[{{ $index }}][start_at]" 
                                                               value="{{ $wf->start_at ? \Carbon\Carbon::parse($wf->start_at)->format('Y-m-d\TH:i') : '' }}" required>
                                                    </td>
                                                    <td>
                                                        <input type="datetime-local" class="form-control form-control-sm" 
                                                               name="schedules[{{ $index }}][end_at]" 
                                                               value="{{ $wf->end_at ? \Carbon\Carbon::parse($wf->end_at)->format('Y-m-d\TH:i') : '' }}" required>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-success mt-2" {{ $war->status === 'active' ? 'disabled' : '' }}>
                                    Save Schedule
                                </button>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.war.update', $war) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Session</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $war->name }}" required>
                </div>
                <div class="form-group">
                    <label>Start At</label>
                    <input type="datetime-local" name="start_at" class="form-control" 
                           value="{{ $war->start_at ? \Carbon\Carbon::parse($war->start_at)->format('Y-m-d\TH:i') : '' }}" required>
                </div>
                <div class="form-group">
                    <label>End At</label>
                    <input type="datetime-local" name="end_at" class="form-control" 
                           value="{{ $war->end_at ? \Carbon\Carbon::parse($war->end_at)->format('Y-m-d\TH:i') : '' }}" required>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-danger" onclick="if(confirm('Delete this session?')) { document.getElementById('delete-form').submit(); }">Delete</button>
                <div>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form id="delete-form" action="{{ route('admin.war.destroy', $war) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    if(jQuery().select2) {
        $(".select2").select2();
    }
</script>
@endpush
