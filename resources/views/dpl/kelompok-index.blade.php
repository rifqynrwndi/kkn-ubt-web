@extends('layouts.app')

@section('title', 'Kelompok Binaan')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Kelompok Binaan</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-header">
                <h4>Daftar Kelompok</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th>Kelompok</th>
                                <th>Desa</th>
                                <th>Kecamatan</th>
                                <th>Kabupaten</th>
                                <th class="text-center" width="90">Anggota</th>
                                <th class="text-center" width="90">Tugas</th>
                                <th class="text-center" width="90">Status</th>
                                <th class="text-center" width="70">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelompoks as $index => $k)
                            @php
                                $tugasTotal = $k->total_tugas ?? 0;
                                $tugasDone = $k->submitted_tugas ?? 0;
                                $tugasPercent = $tugasTotal > 0 ? round(($tugasDone / $tugasTotal) * 100) : 0;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><strong>{{ $k->nama_kelompok }}</strong></td>
                                <td>{{ $k->desaGelombang->desa->nama_desa ?? '-' }}</td>
                                <td>{{ $k->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}</td>
                                <td>{{ $k->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}</td>
                                <td class="text-center">{{ $k->peserta_kkn_count }} / {{ $k->kuota }}</td>
                                <td class="text-center">
                                    @if($tugasTotal > 0)
                                        <span class="badge badge-{{ $tugasPercent == 100 ? 'success' : ($tugasPercent > 0 ? 'warning' : 'danger') }}">
                                            {{ $tugasDone }}/{{ $tugasTotal }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($k->status === 'penuh')
                                        <span class="badge badge-danger">Penuh</span>
                                    @elseif($k->status === 'dibuka')
                                        <span class="badge badge-success">Dibuka</span>
                                    @else
                                        <span class="badge badge-info">{{ $k->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dpl.kelompok.show', $k->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Belum ada kelompok binaan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(isset($semuaTasks) && $semuaTasks->unique('nama_tugas')->count() > 0)
        <div class="card mt-3 border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h4 class="mb-0">Rekap Tugas <small class="text-muted">({{ $kelompoks->count() }} kelompok)</small></h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                    <table class="table table-striped table-hover mb-0">
                        <thead style="background:#2D3A8A;position:sticky;top:0;z-index:1;">
                            <tr>
                                <th class="text-white" width="220">Kelompok</th>
                                @foreach($semuaTasks->unique('nama_tugas') as $wt)
                                <th class="text-white text-center" width="80" style="font-size:10px;">{{ $wt->nama_tugas }}</th>
                                @endforeach
                                <th class="text-white text-center" width="60">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelompoks as $k)
                            <tr>
                                <td><small>{{ $k->nama_kelompok }}</small></td>
                                @php $done = 0; $totalW = $semuaTasks->unique('nama_tugas')->count(); @endphp
                                @foreach($semuaTasks->unique('nama_tugas') as $wt)
                                @php $t = $k->tugasKelompok->firstWhere('nama_tugas', $wt->nama_tugas); $submitted = $t && $t->submissions->isNotEmpty(); if($submitted) $done++; @endphp
                                <td class="text-center">@if($submitted)<span class="badge badge-success">✅</span>@else<span class="badge badge-danger">❌</span>@endif</td>
                                @endforeach
                                <td class="text-center font-weight-bold"><span class="badge badge-{{ $done == $totalW ? 'success' : 'danger' }}">{{ $done }}/{{ $totalW }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>
</section>
@endsection
