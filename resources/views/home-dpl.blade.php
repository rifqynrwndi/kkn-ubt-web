@extends('layouts.app')

@section('title', 'Dashboard DPL')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Dashboard DPL</h1>
    </div>

    <div class="section-body">
        @php
            $dpl = auth()->user()->dosenPembimbingLapangan;
            $kelompoks = $dpl ? $dpl->kelompokKkn()->withCount('pesertaKkn')->get() : collect();
            $totalMahasiswa = $kelompoks->sum('peserta_kkn_count');
        @endphp

        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Kelompok Binaan</h4></div>
                        <div class="card-body">{{ $kelompoks->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>Total Mahasiswa</h4></div>
                        <div class="card-body">{{ $totalMahasiswa }}</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header"><h4>NIDN</h4></div>
                        <div class="card-body" style="font-size:16px;">{{ $dpl->nidn ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Kelompok Binaan</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Kelompok</th>
                                        <th>Desa</th>
                                        <th>Kecamatan</th>
                                        <th>Kabupaten</th>
                                        <th>Anggota</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelompoks as $k)
                                    <tr>
                                        <td><strong>{{ $k->nama_kelompok }}</strong></td>
                                        <td>{{ $k->desaGelombang->desa->nama_desa ?? '-' }}</td>
                                        <td>{{ $k->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}</td>
                                        <td>{{ $k->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}</td>
                                        <td>{{ $k->peserta_kkn_count }} / {{ $k->kuota }}</td>
                                        <td>
                                            @if($k->status === 'penuh')
                                                <span class="badge badge-danger">Penuh</span>
                                            @elseif($k->status === 'dibuka')
                                                <span class="badge badge-success">Dibuka</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $k->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('dpl.kelompok.show', $k->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Anda belum memiliki kelompok binaan.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
