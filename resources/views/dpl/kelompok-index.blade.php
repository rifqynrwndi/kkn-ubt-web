@extends('layouts.app')

@section('title', 'DPL Dashboard')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>DPL Dashboard</h1>
    </div>

    <div class="section-body">
        @php
            $totalKelompok = $kelompoks->count();
            $proposalPending = $kelompoks->where('status_tahap', 1)->count();
            $aktif = $kelompoks->where('status_tahap', 3)->count();
            $selesai = $kelompoks->where('status_tahap', 4)->count();
            $statusLabels = App\Services\StatusService::STAGES;
        @endphp

        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Kelompok</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalKelompok }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Proposal Pending</h4>
                        </div>
                        <div class="card-body">
                            {{ $proposalPending }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-success">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Aktif KKN</h4>
                        </div>
                        <div class="card-body">
                            {{ $aktif }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Selesai</h4>
                        </div>
                        <div class="card-body">
                            {{ $selesai }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Daftar Kelompok Binaan</h4>
                <div style="min-width:250px;">
                    <input type="text" id="dplSearchInput" class="form-control" placeholder="Cari kelompok, desa...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dplKelompokTable">
                        <thead>
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th>Kelompok</th>
                                <th>Desa</th>
                                <th class="text-center" width="90">Anggota</th>
                                <th class="text-center" width="110">Status WAR</th>
                                <th class="text-center" width="120">Status KKN</th>
                                <th class="text-center" width="70">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelompoks as $index => $k)
                            @php
                                $statusInfo = $statusLabels[$k->status_tahap] ?? $statusLabels[0];
                                $warStatusMap = [
                                    'draft' => ['color' => 'secondary', 'label' => 'Draft'],
                                    'dibuka' => ['color' => 'success', 'label' => 'Dibuka'],
                                    'ditutup' => ['color' => 'warning', 'label' => 'Ditutup'],
                                    'penuh' => ['color' => 'danger', 'label' => 'Penuh'],
                                ];
                                $warStatus = $warStatusMap[$k->status] ?? ['color' => 'info', 'label' => $k->status];
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><strong>{{ $k->nama_kelompok }}</strong></td>
                                <td>{{ $k->desaGelombang->desa->nama_desa ?? '-' }}</td>
                                <td class="text-center">{{ $k->peserta_kkn_count }} / {{ $k->kuota }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $warStatus['color'] }}">{{ $warStatus['label'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $statusInfo['color'] }}">{{ $statusInfo['nama'] }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dpl.kelompok.show', $k->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Belum ada kelompok binaan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        document.getElementById('dplSearchInput')?.addEventListener('keyup', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#dplKelompokTable tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
        </script>
    </div>
</section>
@endsection
