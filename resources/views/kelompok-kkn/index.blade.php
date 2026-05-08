@extends('layouts.app')

@section('title', 'Kelompok KKN')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Kelompok KKN</h1>

        <a href="{{ route('kelompok-kkn.create') }}"
           class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i>
            Tambah Kelompok
        </a>
    </div>

    <div class="section-body">

        {{-- FILTER --}}
        <div class="card shadow-sm mb-4">

            <div class="card-body">

                <form method="GET">

                    <div class="row align-items-end">

                        <div class="col-md-5">
                            <div class="form-group mb-md-0">
                                <label>Cari Kelompok</label>

                                <input type="text"
                                       name="search"
                                       class="form-control"
                                       placeholder="Nama kelompok / kode kelompok..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group mb-md-0">
                                <label>Status</label>

                                <select name="status"
                                        class="form-control">

                                    <option value="">
                                        Semua Status
                                    </option>

                                    <option value="draft"
                                        {{ request('status') == 'draft' ? 'selected' : '' }}>
                                        Draft
                                    </option>

                                    <option value="dibuka"
                                        {{ request('status') == 'dibuka' ? 'selected' : '' }}>
                                        Dibuka
                                    </option>

                                    <option value="penuh"
                                        {{ request('status') == 'penuh' ? 'selected' : '' }}>
                                        Penuh
                                    </option>

                                    <option value="ditutup"
                                        {{ request('status') == 'ditutup' ? 'selected' : '' }}>
                                        Ditutup
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
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
                <h4>Daftar Kelompok KKN</h4>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="thead-light">
                            <tr>
                                <th>Kelompok</th>
                                <th>Lokasi</th>
                                <th>DPL</th>
                                <th>Kuota</th>
                                <th>Status</th>
                                <th width="170" class="text-center">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($kelompok as $item)

                                @php
                                    $persentase = $item->kuota > 0
                                        ? min(($item->terisi / $item->kuota) * 100, 100)
                                        : 0;
                                @endphp

                                <tr>

                                    {{-- KELOMPOK --}}
                                    <td style="min-width: 230px;">

                                        <strong class="d-block">
                                            {{ $item->nama_kelompok }}
                                        </strong>

                                        <small class="text-muted d-block">
                                            {{ $item->kode_kelompok }}
                                        </small>

                                        <span class="badge badge-light mt-1">
                                            Kelompok {{ $item->nomor_kelompok }}
                                        </span>

                                    </td>

                                    {{-- LOKASI --}}
                                    <td style="min-width: 220px;">

                                        <strong class="d-block">
                                            {{ $item->desaGelombang->desa->nama_desa }}
                                        </strong>

                                        <small class="text-muted">
                                            {{ $item->desaGelombang->gelombang->nama_gelombang }}
                                        </small>

                                    </td>

                                    {{-- DPL --}}
                                    <td>

                                        @if($item->dosenPembimbingLapangan)

                                            <span>
                                                {{ $item->dosenPembimbingLapangan->user->name }}
                                            </span>

                                        @else

                                            <span class="text-muted">
                                                Belum ada DPL
                                            </span>

                                        @endif

                                    </td>

                                    {{-- KUOTA --}}
                                    <td style="min-width: 180px;">

                                        <div class="d-flex justify-content-between mb-1">

                                            <small>
                                                Terisi
                                            </small>

                                            <small>
                                                {{ $item->terisi }}/{{ $item->kuota }}
                                            </small>

                                        </div>

                                        <div class="progress"
                                             style="height: 8px; border-radius: 10px;">

                                            <div class="progress-bar
                                                {{ $item->is_full ? 'bg-danger' : 'bg-primary' }}"
                                                role="progressbar"
                                                style="width: {{ $persentase }}%">
                                            </div>

                                        </div>

                                        <small class="text-muted">
                                            Sisa {{ $item->sisa_kuota }} slot
                                        </small>

                                    </td>

                                    {{-- STATUS --}}
                                    <td>

                                        @if($item->is_full)

                                            <span class="badge badge-danger">
                                                Penuh
                                            </span>

                                        @elseif($item->status == 'dibuka')

                                            <span class="badge badge-success">
                                                Dibuka
                                            </span>

                                        @elseif($item->status == 'ditutup')

                                            <span class="badge badge-dark">
                                                Ditutup
                                            </span>

                                        @else

                                            <span class="badge badge-secondary">
                                                Draft
                                            </span>

                                        @endif

                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">

                                        <div class="d-flex justify-content-center">

                                            <a href="{{ route('kelompok-kkn.show', $item->id) }}"
                                               class="btn btn-info btn-sm mr-1"
                                               title="Detail">

                                                <i class="fas fa-eye"></i>

                                            </a>

                                            <a href="{{ route('kelompok-kkn.edit', $item->id) }}"
                                               class="btn btn-warning btn-sm mr-1"
                                               title="Edit">

                                                <i class="fas fa-edit"></i>

                                            </a>

                                            <form action="{{ route('kelompok-kkn.destroy', $item->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Hapus kelompok ini?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm"
                                                        title="Hapus">

                                                    <i class="fas fa-trash"></i>

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6"
                                        class="text-center text-muted py-5">

                                        <i class="fas fa-users fa-2x mb-2 d-block"></i>

                                        Belum ada data kelompok KKN.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            @if($kelompok->hasPages())

                <div class="card-footer">
                    {{ $kelompok->links() }}
                </div>

            @endif

        </div>

    </div>

</section>
@endsection
