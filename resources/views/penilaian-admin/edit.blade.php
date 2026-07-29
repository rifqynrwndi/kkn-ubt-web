@extends('layouts.app')

@section('title', 'Input Nilai - ' . $kelompok->nama_kelompok)

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Input Nilai Kelompok</h1>
        <a href="{{ route('penilaian.admin.index', request()->only(['gelombang_id', 'search', 'page'])) }}" class="btn btn-secondary">Kembali</a>
    </div>

    <div class="section-body">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><strong>Kelompok</strong><br>{{ $kelompok->nama_kelompok }}</div>
                    <div class="col-md-3"><strong>Desa</strong><br>{{ $kelompok->desaGelombang?->desa?->nama_desa ?? '-' }}</div>
                    <div class="col-md-3"><strong>Kecamatan</strong><br>{{ $kelompok->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-' }}</div>
                    <div class="col-md-3"><strong>DPL</strong><br>{{ $kelompok->dosenPembimbingLapangan?->user?->name ?? '-' }}</div>
                </div>
            </div>
        </div>

        <form action="{{ route('penilaian.admin.input') }}" method="POST">
            @csrf
            <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok->id }}">
            <input type="hidden" name="gelombang_id" value="{{ request('gelombang_id') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="page" value="{{ request('page') }}">

            @php $desaKom = $komponenList->firstWhere('nama_komponen', 'Nilai Desa'); @endphp
            @if($desaKom)
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h4 class="mb-0">Nilai Desa (Per Mahasiswa)</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:#2D3A8A;">
                                <tr>
                                    <th class="text-white">Mahasiswa</th>
                                    <th class="text-white" width="100">NPM</th>
                                    <th class="text-white text-center" width="120">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelompok->pesertaKkn as $p)
                                @php $existing = $penilaianIndividu[$p->id][$desaKom->id]->nilai ?? null; @endphp
                                <tr>
                                    <td>{{ $p->mahasiswa?->user?->name ?? '-' }}</td>
                                    <td>{{ $p->mahasiswa?->npm ?? '-' }}</td>
                                    <td class="text-center">
                                        <input type="hidden" name="desa_peserta_kkn_id[]" value="{{ $p->id }}">
                                        <input type="number" name="desa_nilai[]" class="form-control form-control-sm text-center" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $existing }}" style="width:90px;">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @foreach($komponenList->where('kategori', 'lppm')->where('nama_komponen', 'Nilai LPPM') as $kom)
            @php $existing = $penilaianKelompok[$kom->id]->nilai ?? null; @endphp
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h4 class="mb-0">{{ $kom->nama_komponen }}</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Nilai Kelompok</label>
                        <input type="hidden" name="komponen_id[]" value="{{ $kom->id }}">
                        <input type="number" name="nilai[]" class="form-control" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $existing }}">
                    </div>
                </div>
            </div>
            @endforeach

            <div class="d-flex justify-content-end">
                <button class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</section>
@endsection