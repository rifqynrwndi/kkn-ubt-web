@extends('layouts.app')

@section('title', 'Penilaian Kelompok')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Penilaian Kelompok</h1>
        @if($selectedGelombang)
        <a href="{{ route('penilaian.admin.export', ['gelombang_id' => $selectedGelombang]) }}" class="btn btn-success">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
        @endif
    </div>

    <div class="section-body">

        {{-- Gelombang Selector --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-5">
                        <label class="form-label font-weight-bold">Pilih Gelombang</label>
                        <select name="gelombang_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Pilih Gelombang --</option>
                            @foreach($gelombangs as $g)
                            <option value="{{ $g->id }}" {{ $g->id == $selectedGelombang ? 'selected' : '' }}>
                                {{ $g->nama_gelombang ?? 'Gelombang ' . $g->id }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label font-weight-bold">Cari</label>
                        <input type="text" name="search" class="form-control" placeholder="Cari kelompok, desa, DPL..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(!$selectedGelombang)
        <div class="card">
            <div class="card-body text-center py-5">
                <span style="font-size:48px;display:block;margin-bottom:16px;">📋</span>
                <h5 class="font-weight-bold mb-2">Pilih Gelombang Terlebih Dahulu</h5>
                <p class="text-muted mb-0">Silakan pilih gelombang KKN untuk melihat daftar penilaian kelompok.</p>
            </div>
        </div>
        @else
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="mb-0">Daftar Nilai Kelompok</h4>
                <span class="text-muted">{{ $kelompoks->total() ?? 0 }} kelompok</span>
            </div>
            <div class="card-body p-0">
                @if($kelompoks->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead style="background:#2D3A8A;">
                            <tr>
                                <th class="text-white text-center" width="40">No</th>
                                <th class="text-white">Kelompok</th>
                                <th class="text-white">Desa</th>
                                <th class="text-white">DPL</th>
                                <th class="text-white text-center" width="90">Nilai DPL</th>
                                <th class="text-white text-center" width="90">Nilai Desa</th>
                                <th class="text-white text-center" width="90">Nilai LPPM</th>
                                <th class="text-white text-center" width="90">Nilai Akhir</th>
                                <th class="text-white text-center" width="100">Aksi</th>
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

                                $desaKom = $komponenList->firstWhere('nama_komponen', 'Nilai Desa');
                                $desaScores = $pi->where('komponen_id', $desaKom?->id)->pluck('nilai');
                                $desaVal = $desaScores->isNotEmpty() ? round($desaScores->avg(), 2) : null;
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
                @else
                <div class="text-center text-muted py-5">Tidak ada kelompok untuk gelombang yang dipilih.</div>
                @endif
            </div>
            @if(method_exists($kelompoks, 'links'))
            <div class="card-footer">
                {{ $kelompoks->links() }}
            </div>
            @endif
        </div>
        @endif
    </div>
</section>

{{-- MODALS --}}
@if($selectedGelombang)
@foreach($kelompoks as $k)
@php
    $pd = \App\Models\PenilaianKelompok::where('kelompok_kkn_id', $k->id)->get()->keyBy('komponen_id');
    $pi = \App\Models\PenilaianIndividu::where('kelompok_kkn_id', $k->id)->get()->groupBy('peserta_kkn_id');
    $k->load('pesertaKkn.mahasiswa.user');
@endphp
<div class="modal fade" id="nilaiModal{{ $k->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#2D3A8A;color:#fff;">
                <h5 class="modal-title">Input Nilai - {{ $k->nama_kelompok }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('penilaian.admin.input') }}" method="POST">
                @csrf
                <input type="hidden" name="kelompok_kkn_id" value="{{ $k->id }}">
                <div class="modal-body">
                    @php $desaKom = $komponenList->firstWhere('nama_komponen', 'Nilai Desa'); @endphp
                    @if($desaKom)
                    <div class="mb-4">
                        <h6 class="font-weight-bold mb-3">Nilai Desa (Per Mahasiswa)</h6>
                        <table class="table table-sm table-bordered mb-0">
                            <thead style="background:#2D3A8A;">
                                <tr>
                                    <th class="text-white">Mahasiswa</th>
                                    <th class="text-white text-center" width="120">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($k->pesertaKkn as $p)
                                @php
                                    $existing = $pi[$p->id][$desaKom->id]->nilai ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $p->mahasiswa?->user?->name ?? '-' }} <small class="text-muted">({{ $p->mahasiswa?->npm ?? '-' }})</small></td>
                                    <td class="text-center">
                                        <input type="hidden" name="desa_peserta_kkn_id[]" value="{{ $p->id }}">
                                        <input type="number" name="desa_nilai[]" class="form-control form-control-sm text-center" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $existing }}" style="width:90px;">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @foreach($komponenList->where('kategori', 'lppm')->where('nama_komponen', 'Nilai LPPM') as $kom)
                    @php $existing = $pd[$kom->id]->nilai ?? null; @endphp
                    <div class="form-group">
                        <label class="font-weight-bold">{{ $kom->nama_komponen }}</label>
                        <input type="hidden" name="komponen_id[]" value="{{ $kom->id }}">
                        <input type="number" name="nilai[]" class="form-control" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $existing }}">
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif
@endsection

@push('scripts')
<script>
$(document).on('hidden.bs.modal', '.modal', function () {
    $('body').removeClass('modal-open');
    $('.modal-backdrop').remove();
});
</script>
@endpush
