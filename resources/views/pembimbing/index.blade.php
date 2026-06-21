@extends('layouts.app')

@section('title', 'Dosen Pembimbing Lapangan')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between">
        <h1>Dosen Pembimbing Lapangan</h1>

        <a href="{{ route('pembimbing-lapangan.create') }}"
           class="btn btn-primary">
            Tambah DPL
        </a>
    </div>

    <div class="section-body">

        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" class="form-inline">
                    <div class="input-group input-group-sm" style="max-width:400px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, email, NIDN, no HP, atau fakultas..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            @if(request('search'))
                            <a href="{{ route('pembimbing-lapangan.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-hover mb-0">

                        <thead>
                            <tr>
                                <th width="50">Foto</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Fakultas</th>
                                <th>NIDN</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($dpl as $item)
                                <tr>

                                    <td>
                                        <img src="{{ $item->foto ? storage_url($item->foto) : asset('img/avatar/avatar-1.png') }}" class="rounded-circle" width="36" height="36" style="object-fit:cover;">
                                    </td>

                                    <td>
                                        {{ $item->user->name }}
                                    </td>

                                    <td>
                                        {{ $item->user->email }}
                                    </td>

                                    <td>
                                        {{ $item->no_hp ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->fakultas?->nama_fakultas ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->nidn ?? '-' }}
                                    </td>

                                    <td>
                                        @if($item->status === 'aktif')
                                            <span class="badge badge-success">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="badge badge-primary">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="d-flex">

                                            {{-- DETAIL --}}
                                            <a href="{{ route('pembimbing-lapangan.show', $item->id) }}"
                                            class="btn btn-info btn-sm mr-1">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- EDIT --}}
                                            <a href="{{ route('pembimbing-lapangan.edit', $item->id) }}"
                                            class="btn btn-warning btn-sm mr-1">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            {{-- DELETE --}}
                                            <form action="{{ route('pembimbing-lapangan.destroy', $item->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Hapus DPL ini?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8"
                                        class="text-center text-muted py-4">
                                        Belum ada data DPL.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

            </div>

            <div class="card-footer">
                {{ $dpl->links() }}
            </div>

        </div>

    </div>
</section>
@endsection
