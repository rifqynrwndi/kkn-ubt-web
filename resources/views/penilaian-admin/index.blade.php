@extends('layouts.app')

@section('title', 'Penilaian Kelompok')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Penilaian Kelompok</h1>
        <div>
            <a href="{{ route('penilaian.admin.export', ['gelombang_id' => $selectedGelombang]) }}" class="btn btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="section-body">
        {{-- GELOMBANG SELECTOR --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-3">
                <form method="GET" class="form-inline">
                    <label class="font-weight-bold mr-2">Pilih Gelombang:</label>
                    <select name="gelombang_id" class="form-control mr-3" onchange="this.form.submit()">
                        @foreach($gelombangs as $g)
                        <option value="{{ $g->id }}" {{ $g->id == $selectedGelombang ? 'selected' : '' }}>
                            {{ $g->nama_gelombang ?? 'Gelombang ' . $g->id }}
                        </option>
                        @endforeach
                    </select>
                    <div class="input-group" style="min-width:200px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari kelompok, desa, DPL..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Daftar Nilai Kelompok</h4>
                <span class="text-muted">{{ $kelompoks->total() ?? 0 }} kelompok</span>
            </div>
            <div class="card-body p-0">
                @if($kelompoks->count())
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead style="background:#2D3A8A;">
                            <tr>
                                <th class="text-white text-center py-3" width="40">No</th>
                                <th class="text-white py-3">Kelompok</th>
                                <th class="text-white py-3">Desa</th>
                                <th class="text-white py-3">DPL</th>
                                <th class="text-white text-center py-3" width="90">Nilai DPL</th>
                                <th class="text-white text-center py-3" width="90">Nilai Desa</th>
                                <th class="text-white text-center py-3" width="90">Nilai LPPM</th>
                                <th class="text-white text-center py-3" width="90">Nilai Akhir</th>
                                <th class="text-white text-center py-3" width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelompoks as $i => $k)
                            @php
                                $pd = \App\Models\PenilaianKelompok::where('kelompok_kkn_id', $k->id)->get()->keyBy('komponen_id');
                                $pi = \App\Models\PenilaianIndividu::where('kelompok_kkn_id', $k->id)->get();

                                $dplKom = $komponenList->firstWhere('nama_komponen', 'Nilai DPL');
                                $dplScores = $pi->where('komponen_id', $dplKom?->id)->pluck('nilai');
                                $dplVal = $dplScores->isNotEmpty() ? round($dplScores->avg(), 2) : null;

                                $desaVal = $pd->first(fn($v) => $v->komponen->nama_komponen === 'Nilai Desa')?->nilai;
                                $lppmVal = $pd->first(fn($v) => $v->komponen->nama_komponen === 'Nilai LPPM')?->nilai;

                                $finalVal = (!is_null($dplVal) && !is_null($desaVal) && !is_null($lppmVal))
                                    ? round($dplVal * 0.40 + $desaVal * 0.30 + $lppmVal * 0.30, 2)
                                    : null;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $kelompoks->firstItem() + $i }}</td>
                                <td>
                                    <strong>{{ $k->nama_kelompok }}</strong>
                                    <br><small class="text-muted">{{ $k->kode_kelompok }}</small>
                                </td>
                                <td>{{ $k->desaGelombang?->desa?->nama_desa ?? '-' }}</td>
                                <td>{{ $k->dosenPembimbingLapangan?->user?->name ?? '-' }}</td>
                                <td class="text-center">{{ $dplVal !== null ? number_format($dplVal, 2) : '-' }}</td>
                                <td class="text-center">{{ $desaVal !== null ? number_format($desaVal, 2) : '-' }}</td>
                                <td class="text-center">{{ $lppmVal !== null ? number_format($lppmVal, 2) : '-' }}</td>
                                <td class="text-center font-weight-bold">{{ $finalVal !== null ? number_format($finalVal, 2) : '-' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#nilaiModal{{ $k->id }}">
                                        <i class="fas fa-edit"></i> Input
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 d-flex justify-content-center">
                    {{ $kelompoks->links() }}
                </div>
                @else
                <div class="text-center text-muted py-5">Tidak ada kelompok untuk gelombang yang dipilih.</div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- MODALS --}}
@foreach($kelompoks as $k)
@php
    $pd = \App\Models\PenilaianKelompok::where('kelompok_kkn_id', $k->id)->get()->keyBy('komponen_id');
@endphp
<div class="modal fade" id="nilaiModal{{ $k->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#2D3A8A;color:#fff;">
                <h5 class="modal-title">Input Nilai - {{ $k->nama_kelompok }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                @foreach($komponenList->where('kategori', 'lppm') as $kom)
                @php $existing = $pd[$kom->id]->nilai ?? null; @endphp
                <form action="{{ route('penilaian.admin.input') }}" method="POST" class="mb-3">
                    @csrf
                    <input type="hidden" name="kelompok_kkn_id" value="{{ $k->id }}">
                    <input type="hidden" name="komponen_id" value="{{ $kom->id }}">
                    <div class="form-group">
                        <label class="font-weight-bold">{{ $kom->nama_komponen }}</label>
                        <div class="input-group">
                            <input type="number" name="nilai" class="form-control" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $existing }}" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                            </div>
                        </div>
                    </div>
                </form>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
