@extends('layouts.app')

@section('title', 'Penilaian Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Penilaian Mahasiswa</h1>
    </div>

    <div class="section-body">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h4 class="mb-0">Daftar Kelompok Binaan</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead style="background:#2D3A8A;">
                            <tr>
                                <th class="text-white text-center py-3" width="40">No</th>
                                <th class="text-white py-3">Kelompok</th>
                                <th class="text-white py-3">Desa</th>
                                <th class="text-white py-3">Kecamatan</th>
                                <th class="text-white text-center py-3" width="90">Anggota</th>
                                <th class="text-white text-center py-3" width="70">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelompoks as $i => $k)
                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td><strong>{{ $k->nama_kelompok }}</strong></td>
                                <td>{{ $k->desaGelombang?->desa?->nama_desa ?? '-' }}</td>
                                <td>{{ $k->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-' }}</td>
                                <td class="text-center">{{ $k->pesertaKkn->count() }} / {{ $k->kuota }}</td>
                                <td class="text-center">
                                    <a href="{{ route('dpl.penilaian.show', $k->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-pen"></i> Nilai
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kelompok binaan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
