@extends('layouts.app')

@section('title', 'Hak Akses')

<style>
    .table td, .table th {
        vertical-align: middle !important;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .name-column {
        white-space: normal !important;
        min-width: 150px;
        max-width: 200px;
        word-break: break-word;
        line-height: 1.4;
    }
    .action-column {
        white-space: nowrap !important;
        min-width: 110px;
    }
</style>

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Hak Akses</h1>
    </div>

    <div class="card">
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- Search + Filters --}}
            <form method="GET" class="mb-3">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama / email..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary px-3" type="submit"><i class="fas fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <select name="role" class="form-control" onchange="this.form.submit()">
                            <option value="">Semua Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(request()->hasAny(['search', 'role']))
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('hakakses.index') }}" class="btn btn-outline-secondary btn-block">
                                <i class="fas fa-times"></i> Reset
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-md">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th class="text-center">Role</th>
                            <th class="text-center action-column">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hakakses as $index => $user)
                        <tr>
                            <td class="text-center">
                                {{ $hakakses->firstItem() + $index }}
                            </td>
                            <td class="name-column">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">
                                @php($roleName = $user->getRoleNames()->first() ?? '-')
                                @if($roleName === 'superadmin')
                                    <span class="badge badge-danger">{{ ucfirst($roleName) }}</span>
                                @elseif($roleName === 'admin_lppm')
                                    <span class="badge badge-warning">{{ ucfirst(str_replace('_', ' ', $roleName)) }}</span>
                                @elseif($roleName === 'admin_prodi')
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $roleName)) }}</span>
                                @elseif($roleName === 'pembimbing')
                                    <span class="badge badge-primary">{{ ucfirst($roleName) }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($roleName) }}</span>
                                @endif
                            </td>
                            <td class="text-center action-column">
                                <a href="{{ route('hakakses.edit', $user->id) }}"
                                   class="btn btn-warning btn-sm"
                                   data-toggle="tooltip"
                                   title="Edit Role">
                                    <i class="fas fa-user-edit"></i> Edit
                                </a>

                                <form action="{{ route('hakakses.destroy', $user->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus pengguna ini?')"
                                            data-toggle="tooltip"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Data tidak ditemukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4" style="display: flex; justify-content: center;">
                {{ $hakakses->links() }}
            </div>

        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@endpush
