@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Dashboard Admin</h1>
    </div>

    <div class="section-body">

        {{-- STATISTIK RINGKAS --}}
        <div class="row">

            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Mahasiswa</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['total_mahasiswa'] }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Email Terverifikasi</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['verified'] }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Biodata Belum Lengkap</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['incomplete_biodata'] }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Gelombang Aktif</h4>
                        </div>
                        <div class="card-body">
                            {{ $stats['active_gelombang'] }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- REMINDER --}}
        @if(count($reminders))
            <div class="row">
                <div class="col-12">
                    @foreach($reminders as $reminder)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ $reminder }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="row">

            {{-- BAR CHART PENDAFTARAN --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Pendaftaran per Gelombang</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="gelombangChart" height="120"></canvas>
                    </div>
                </div>
            </div>

            {{-- MAHASISWA TERBARU --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Mahasiswa Terbaru</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($latestMahasiswa as $mahasiswa)
                                        <tr>
                                            <td>{{ $mahasiswa->name }}</td>
                                            <td>{{ $mahasiswa->email }}</td>
                                            <td>{{ $mahasiswa->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                Belum ada data mahasiswa
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


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('gelombangChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($gelombangChart['labels']),
        datasets: [{
            label: 'Jumlah Pendaftar',
            data: @json($gelombangChart['data']),
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>
@endpush
