@extends('layouts.app')

@section('title', 'Data Desa')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Data Desa</h1>
        <div>
            <a href="{{ route('desa.createKecamatan') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>
                Tambah Kecamatan
            </a>

            <a href="{{ route('desa.create') }}"
                class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i>
                    Tambah Desa
            </a>
        </div>

    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <form method="GET">

                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group mb-md-0">
                                <label>Cari Desa</label>

                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       value="{{ request('search') }}"
                                       placeholder="Cari nama desa...">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-md-0">
                                <label>Kecamatan</label>

                                <select name="kecamatan_id"
                                        class="form-control">
                                    <option value="">
                                        Semua Kecamatan
                                    </option>

                                    @foreach($kecamatan as $item)
                                        <option value="{{ $item->id }}"
                                            {{ request('kecamatan_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_kecamatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-md-0">
                                <label>Status</label>

                                <select name="aktif"
                                        class="form-control">
                                    <option value="">
                                        Semua
                                    </option>

                                    <option value="1"
                                        {{ request('aktif') === '1' ? 'selected' : '' }}>
                                        Aktif
                                    </option>

                                    <option value="0"
                                        {{ request('aktif') === '0' ? 'selected' : '' }}>
                                        Nonaktif
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary btn-block">
                                <i class="fas fa-search mr-1"></i>
                                Filter
                            </button>
                        </div>

                    </div>

                </form>

            </div>
        </div>

        {{-- TABLE --}}
        <div class="card shadow-sm">

            <div class="card-header">
                <h4>Daftar Desa</h4>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>
                            <tr>
                                <th>Desa</th>
                                <th>Kecamatan</th>
                                <th>Status</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($desa as $item)

                                <tr>

                                    <td>
                                        <strong>
                                            {{ $item->nama_desa }}
                                        </strong>

                                        @if($item->alamat)
                                            <br>
                                            <small class="text-muted">
                                                {{ $item->alamat }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $item->kecamatan->nama_kecamatan }}
                                    </td>

                                    <td>
                                        @if($item->aktif)
                                            <span class="badge badge-success">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{-- // buat jarak antar button --}}
                                        <div class="d-flex gap-2">

                                            {{-- DETAIL --}}
                                            <a href="{{ route('desa.show', $item->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- EDIT --}}
                                            <a href="{{ route('desa.edit', $item->id) }}"
                                                class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a href="{{ route('desa.destroy', $item->id) }}"
                                                class="btn btn-danger btn-sm"
                                                onclick="event.preventDefault(); if(confirm('Hapus desa ini?')) { document.getElementById('delete-form-{{ $item->id }}').submit(); }">
                                                <i class="fas fa-trash"></i>
                                            </a>

                                        </div>




                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5"
                                        class="text-center text-muted py-4">
                                        Belum ada data desa.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            @if(method_exists($desa, 'links'))
                <div class="card-footer">
                    {{ $desa->links() }}
                </div>
            @endif

        </div>

    </div>
</section>
@endsection
