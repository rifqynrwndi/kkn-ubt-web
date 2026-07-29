@extends('layouts.app')

@section('title', 'Penilaian - ' . $kelompok->nama_kelompok)

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-0">Input Nilai Per Anggota</h1>
            <small class="text-muted">{{ $kelompok->nama_kelompok }} — {{ $kelompok->desaGelombang?->desa?->nama_desa ?? '-' }}</small>
        </div>
        <a href="{{ route('dpl.penilaian.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    <div class="section-body">
        @if($komponenList->count())
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead style="background:#2D3A8A;">
                            <tr>
                                <th class="text-white py-3" width="200"><i class="fas fa-user mr-2"></i>Anggota</th>
                                @foreach($komponenList as $k)
                                <th class="text-white text-center py-3" width="200">{{ $k->nama_komponen }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kelompok->pesertaKkn as $p)
                            <tr>
                                <td class="py-2">
                                    <strong class="d-block">{{ $p->mahasiswa?->user?->name ?? '-' }}</strong>
                                    <small class="text-muted">{{ $p->mahasiswa?->npm ?? '-' }}</small>
                                </td>
                                @foreach($komponenList as $k)
                                @php $nilai = $penilaianIndividu[$p->id][$k->id]->nilai ?? null; @endphp
                                <td class="text-center py-2">
                                    <form action="{{ route('dpl.penilaian.input') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok->id }}">
                                        <input type="hidden" name="komponen_id" value="{{ $k->id }}">
                                        <input type="hidden" name="peserta_kkn_id" value="{{ $p->id }}">
                                        <div class="d-flex justify-content-center">
                                            <input type="number" name="nilai" class="form-control form-control-sm text-center" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width:80px;">
                                            <button class="btn btn-sm btn-primary ml-1"><i class="fas fa-save"></i></button>
                                        </div>
                                    </form>
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                            @if($kelompok->pesertaKkn->count() === 0)
                            <tr><td colspan="{{ $komponenList->count() + 1 }}" class="text-center text-muted py-4">Belum ada anggota.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">⭐</span><h5>Belum Ada Komponen Penilaian</h5><p class="text-muted">Jalankan seeder PenilaianKomponenSeeder terlebih dahulu.</p></div></div>
        @endif
    </div>
</section>
@endsection
