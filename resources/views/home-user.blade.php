@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Dashboard Mahasiswa</h1>
    </div>

    <div class="section-body">

        {{-- STATUS CARDS --}}
        <div class="row">

            <div class="col-lg-4 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Status Biodata</h4>
                        </div>
                        <div class="card-body">
                            {{ $biodataComplete ? 'Lengkap' : 'Belum Lengkap' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-success">
                        <i class="fas fa-file-signature"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Status Pendaftaran</h4>
                        </div>
                        <div class="card-body">
                            {{ $registrationStatus }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 col-12">
                <div class="card card-statistic-1">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Gelombang Aktif</h4>
                        </div>
                        <div class="card-body">
                            {{ $activeGelombang ?? '-' }}
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

            {{-- DETAIL PENDAFTARAN --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Informasi Pendaftaran KKN</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="200">Gelombang</th>
                                <td>{{ $pendaftaran?->gelombang?->nama_gelombang ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status Verifikasi</th>
                                <td>{{ $pendaftaran?->status ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Tanggal Daftar</th>
                                <td>
                                    {{ $pendaftaran?->created_at?->format('d M Y H:i') ?? '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- NOTIFIKASI TERBARU --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Notifikasi Terbaru</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($recentNotifications as $notif)
                                <li class="list-group-item">
                                    <strong>{{ $notif->data['title'] }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $notif->created_at->diffForHumans() }}
                                    </small>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center">
                                    Tidak ada notifikasi
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>
@endsection
