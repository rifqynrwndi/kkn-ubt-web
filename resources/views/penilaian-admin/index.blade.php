@extends('layouts.app')

@section('title', 'Penilaian Kelompok')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Penilaian Kelompok</h1>
        <a href="{{ route('penilaian.admin.export') }}" class="btn btn-success">
            <i class="fas fa-file-excel mr-1"></i> Export Excel
        </a>
    </div>

    <div class="section-body">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h4 class="mb-0">Daftar Nilai Kelompok KKN</h4>
            </div>
            <div class="card-body p-0">
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
                                <th class="text-white text-center py-3" width="120">Nilai LPPM</th>
                                <th class="text-white text-center py-3" width="100">Nilai Akhir</th>
                                <th class="text-white text-center py-3" width="160">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelompoks as $i => $k)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $k->nama_kelompok }}</strong>
                                    <br><small class="text-muted">{{ $k->kode_kelompok }}</small>
                                </td>
                                <td>{{ $k->desaGelombang?->desa?->nama_desa ?? '-' }}</td>
                                <td>{{ $k->dosenPembimbingLapangan?->user?->name ?? '-' }}</td>
                                <td class="text-center">
                                    @if($k->dplScore !== null)
                                    <span class="font-weight-bold {{ $k->dplScore>=75?'text-success':($k->dplScore>=60?'text-warning':'text-danger') }}">{{ number_format($k->dplScore, 2) }}</span>
                                    @else <span class="text-muted">-</span> @endif
                                </td>
                                <td class="text-center">
                                    @if($k->desaScore !== null)
                                    <span class="font-weight-bold {{ $k->desaScore>=75?'text-success':($k->desaScore>=60?'text-warning':'text-danger') }}">{{ number_format($k->desaScore, 2) }}</span>
                                    @else <span class="text-muted">-</span> @endif
                                </td>
                                <td class="text-center">
                                    @if($k->lppmScore !== null)
                                    <span class="font-weight-bold {{ $k->lppmScore>=75?'text-success':($k->lppmScore>=60?'text-warning':'text-danger') }}">{{ number_format($k->lppmScore, 2) }}</span>
                                    @else
                                    <form action="{{ route('penilaian.admin.input') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="kelompok_kkn_id" value="{{ $k->id }}">
                                        <div class="input-group input-group-sm" style="min-width:120px;">
                                            <input type="number" name="nilai" class="form-control form-control-sm text-center" placeholder="0-100" min="0" max="100" step="0.01" style="width:60px;">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                                            </div>
                                        </div>
                                    </form>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($k->finalScore !== null)
                                    <span class="font-weight-bold" style="font-size:1.1rem;color:#2D3A8A;">{{ number_format($k->finalScore, 2) }}</span>
                                    @else <span class="text-muted">-</span> @endif
                                </td>
                                <td class="text-center">
                                    @if($k->lppmScore !== null)
                                    <form action="{{ route('penilaian.admin.input') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="kelompok_kkn_id" value="{{ $k->id }}">
                                        <div class="input-group input-group-sm" style="min-width:120px;">
                                            <input type="number" name="nilai" class="form-control form-control-sm text-center" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $k->lppmScore }}" style="width:60px;">
                                            <div class="input-group-append">
                                                <button class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </div>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">Belum ada kelompok KKN.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
