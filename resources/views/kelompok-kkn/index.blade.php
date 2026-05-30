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

        {{-- FILTER Kabupaten --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center gap-1">
                    <a href="{{ route('kelompok-kkn.index') }}" class="btn btn-sm {{ !request('kabupaten') && !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} mr-1 mb-1">
                        <i class="fas fa-th-list mr-1"></i> Semua
                    </a>
                    @foreach($kabupatens as $kab)
                        <a href="{{ route('kelompok-kkn.index', array_filter(array_merge(request()->all(), ['kabupaten' => $kab, 'kecamatan_id' => null, 'search' => null]))) }}" class="btn btn-sm {{ request('kabupaten') == $kab ? 'btn-primary' : 'btn-outline-secondary' }} mr-1 mb-1">
                            {{ $kab }}
                        </a>
                    @endforeach
                    <div class="dropdown ml-auto mb-1">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                            {{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('kelompok-kkn.index', array_filter(array_merge(request()->except('status')))) }}">Semua Status</a>
                            <a class="dropdown-item" href="{{ route('kelompok-kkn.index', array_merge(request()->all(), ['status' => 'dibuka'])) }}">Dibuka</a>
                            <a class="dropdown-item" href="{{ route('kelompok-kkn.index', array_merge(request()->all(), ['status' => 'penuh'])) }}">Penuh</a>
                            <a class="dropdown-item" href="{{ route('kelompok-kkn.index', array_merge(request()->all(), ['status' => 'ditutup'])) }}">Ditutup</a>
                            <a class="dropdown-item" href="{{ route('kelompok-kkn.index', array_merge(request()->all(), ['status' => 'draft'])) }}">Draft</a>
                        </div>
                    </div>
                </div>
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
                                <th>Kabupaten / Kecamatan</th>
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

                                    {{-- KABUPATEN / KECAMATAN --}}
                                    <td style="min-width: 220px;">
                                        <strong class="d-block">
                                            {{ $item->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}
                                        </strong>
                                        <small class="text-muted">
                                            {{ $item->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}
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
