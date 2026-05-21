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
                                <th>No</th>
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
                            @forelse($kelompoks as $index => $k)
                            <tr>
                                <td>{{ $index + 1 }}</td>
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
                                <td colspan="8" class="text-center text-muted py-4">
                                    Belum ada kelompok binaan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
