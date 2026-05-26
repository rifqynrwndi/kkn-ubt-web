@extends('layouts.app')

@section('title', 'Detail Kelompok KKN')

@section('content')
<section class="section">

    {{-- HEADER --}}
    <div class="section-header d-flex justify-content-between align-items-center">

        <div>
            <h1 class="mb-0">
                {{ $kelompok_kkn->nama_kelompok }}
            </h1>

            <small class="text-muted">
                {{ $kelompok_kkn->kode_kelompok }}
            </small>
        </div>

        <div class="d-flex align-items-center">

            @if($kelompok_kkn->status == 'dibuka')
                <form action="{{ route('kelompok-kkn.tutup', $kelompok_kkn->id) }}"
                      method="POST"
                      class="mr-2">
                    @csrf
                    @method('PUT')

                    <button class="btn btn-danger">
                        <i class="fas fa-lock mr-1"></i>
                        Tutup War
                    </button>
                </form>

            @elseif(!$kelompok_kkn->is_full)
                <form action="{{ route('kelompok-kkn.buka', $kelompok_kkn->id) }}"
                      method="POST"
                      class="mr-2">
                    @csrf
                    @method('PUT')

                    <button class="btn btn-success">
                        <i class="fas fa-lock-open mr-1"></i>
                        Buka War
                    </button>
                </form>
            @endif

            <a href="{{ route('kelompok-kkn.edit', $kelompok_kkn->id) }}"
               class="btn btn-warning mr-2">
                <i class="fas fa-edit mr-1"></i>
                Edit
            </a>

            <a href="{{ route('kelompok-kkn.index') }}"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Kembali
            </a>

        </div>

    </div>

    <div class="section-body">

        {{-- STATISTIK --}}
        <div class="row">

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Anggota</h4>
                        </div>

                        <div class="card-body">
                            {{ $kelompok_kkn->terisi }}/{{ $kelompok_kkn->kuota }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-success">
                        <i class="fas fa-home"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Desa</h4>
                        </div>

                        <div class="card-body">
                            {{ $kelompok_kkn->desaGelombang?->desa?->nama_desa ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-tie"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>DPL</h4>
                        </div>

                        <div class="card-body">
                            {{ $kelompok_kkn->dosenPembimbingLapangan?->user?->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-chart-pie"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Status</h4>
                        </div>

                        <div class="card-body">
                            @if($kelompok_kkn->is_full)
                                <span class="badge badge-danger">Penuh</span>
                            @elseif($kelompok_kkn->status == 'dibuka')
                                <span class="badge badge-success">Dibuka</span>
                            @elseif($kelompok_kkn->status == 'ditutup')
                                <span class="badge badge-dark">Ditutup</span>
                            @else
                                <span class="badge badge-secondary">Draft</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- PROGRESS --}}
        @php
            $persentase = $kelompok_kkn->kuota > 0
                ? ($kelompok_kkn->terisi / $kelompok_kkn->kuota) * 100
                : 0;
        @endphp

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Kapasitas Kelompok</strong>

                    <strong>
                        {{ $kelompok_kkn->terisi }}/{{ $kelompok_kkn->kuota }}
                    </strong>
                </div>

                <div class="progress" style="height: 20px;">
                    <div class="progress-bar {{ $kelompok_kkn->is_full ? 'bg-danger' : 'bg-primary' }}"
                         style="width: {{ min($persentase, 100) }}%">
                        {{ round($persentase) }}%
                    </div>
                </div>

                <div class="mt-3">
                    <span class="badge badge-primary">
                        Terisi: {{ $kelompok_kkn->terisi }}
                    </span>

                    <span class="badge badge-success">
                        Sisa: {{ $kelompok_kkn->sisa_kuota }}
                    </span>

                    @if($kelompok_kkn->is_full)
                        <span class="badge badge-danger">
                            Kelompok Penuh
                        </span>
                    @endif
                </div>

            </div>
        </div>

        {{-- INFORMASI PENEMPATAN --}}
        <div class="card shadow-sm">

            <div class="card-header">
                <h4 class="mb-0">
                    Informasi Penempatan
                </h4>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">
                        <strong>Desa</strong>
                        <div>
                            {{ $kelompok_kkn->desaGelombang?->desa?->nama_desa ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <strong>Gelombang</strong>
                        <div>
                            {{ $kelompok_kkn->desaGelombang?->gelombang?->nama_gelombang ?? '-' }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <strong>DPL</strong>
                        <div>
                            {{ $kelompok_kkn->dosenPembimbingLapangan?->user?->name ?? '-' }}
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- ANGGOTA --}}
        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

                <div class="d-flex align-items-center">

                    <h4 class="mb-0 mr-3">
                        Anggota Kelompok
                    </h4>

                    <span class="badge badge-primary">
                        {{ $kelompok_kkn->terisi }} Anggota
                    </span>

                </div>

            @if(!$kelompok_kkn->is_full)

                <a href="{{ route('kelompok-kkn.anggota.create', $kelompok_kkn->id) }}"
                class="btn btn-primary btn-sm">

                    <i class="fas fa-user-plus mr-1"></i>
                    Tambah Anggota

                </a>

            @endif

        </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Nama</th>
                                <th>NPM</th>
                                <th>Fakultas</th>
                                <th>Program Studi</th>
                                <th width="170">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($kelompok_kkn->pesertaKkn as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ $item->mahasiswa?->user?->name ?? '-' }}
                                        @if($item->id === $kelompok_kkn->ketua_peserta_id)
                                            <span class="badge badge-warning ml-1" style="font-size:10px;border-radius:8px;">Ketua</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $item->mahasiswa?->npm ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->mahasiswa?->prodi?->nama_prodi ?? '-' }}
                                    </td>

                                    <td>
                                    <div class="d-flex gap-1">
                                    @if($item->id !== $kelompok_kkn->ketua_peserta_id)
                                    <form action="{{ route('kelompok-kkn.ketua', ['kelompok_kkn' => $kelompok_kkn->id, 'peserta' => $item->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Jadikan anggota ini sebagai ketua kelompok?')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                                class="btn btn-warning btn-sm"
                                                title="Jadikan Ketua">
                                            <i class="fas fa-crown"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('kelompok-kkn.anggota.destroy', ['kelompok_kkn' => $kelompok_kkn->id,'peserta' => $item->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Keluarkan anggota dari kelompok?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-danger btn-sm"
                                                title="Hapus Anggota">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="text-center text-muted py-4">
                                        Belum ada anggota kelompok.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        {{-- ============================================ --}}
        {{-- POST-WAR MODULES --}}
        {{-- ============================================ --}}

        {{-- STATUS STAGE --}}
        <div class="card shadow-sm mt-3">
            <div class="card-header"><h5><i class="fas fa-tasks mr-2"></i> Status Tahap KKN</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-center flex-wrap gap-1 mb-3">
                    @foreach($statusStages as $i => $s)
                    <div class="text-center px-2" style="min-width:85px;">
                        <div style="width:30px;height:30px;border-radius:50%;margin:0 auto 3px;background:{{ $i <= (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#e0e0e0' }};color:{{ $i <= (int)$kelompok_kkn->status_tahap ? '#fff' : '#999' }};font-weight:800;font-size:12px;line-height:30px;">{{ $i }}</div>
                        <small style="font-size:9px;color:{{ $i === (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#adb5bd' }};font-weight:{{ $i === (int)$kelompok_kkn->status_tahap ? '700' : '400' }};">{{ $s['nama'] }}</small>
                    </div>
                    @if($i < 7)<div style="width:20px;height:2px;background:{{ $i < (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#e0e0e0' }};margin-top:14px;flex-shrink:0;"></div>@endif
                    @endforeach
                </div>
                <div class="alert alert-primary text-center mb-0"><strong>Tahap Saat Ini: {{ $statusCurrent['nama'] }}</strong></div>
            </div>
        </div>

        {{-- PROPOSAL --}}
        @if($proposal)
        <div class="card shadow-sm mt-3">
            <div class="card-header"><h5><i class="fas fa-file-alt mr-2"></i> Proposal — <span class="badge badge-{{ $proposal->status==='disetujui'?'success':($proposal->status==='ditolak'?'danger':'info') }}">{{ $proposal->status }}</span></h5></div>
            <div class="card-body">
                @foreach(['pendahuluan'=>'Pendahuluan','tujuan'=>'Tujuan','manfaat'=>'Manfaat','hasil_observasi'=>'Hasil Observasi','rancangan_program'=>'Rancangan Program','solusi_ide'=>'Solusi & Ide'] as $f=>$l)
                <h6 class="font-weight-bold text-primary">{{ $l }}</h6>
                <p style="font-size:13px;text-align:justify;">{!! $proposal->$f ?: '<span class="text-muted">—</span>' !!}</p>
                @endforeach
            </div>
        </div>
        @endif

        {{-- TUGAS --}}
        @if($tugasList->count())
        <div class="card shadow-sm mt-3">
            <div class="card-header"><h5><i class="fas fa-upload mr-2"></i> Pengumpulan Tugas</h5></div>
            <div class="card-body p-0">
                @foreach($tugasList as $kat => $items)
                <div class="p-3 border-bottom"><strong>{{ ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Lain','laporan'=>'Laporan'][$kat] ?? $kat }}</strong></div>
                @foreach($items as $t)
                <div class="px-3"><strong class="small">{{ $t->nama_tugas }}</strong>
                    @if($t->submissions->count())
                    <table class="table table-sm"><tr><th>Judul</th><th>Oleh</th><th>Status</th></tr>
                    @foreach($t->submissions as $s)
                    <tr><td>{{ $s->judul }}</td><td>{{ $s->pesertaKkn->mahasiswa->user->name ?? '-' }}</td><td><span class="badge badge-{{ $s->status==='diterima'?'success':($s->status==='ditolak'?'danger':'info') }}">{{ $s->status }}</span></td></tr>
                    @endforeach</table>
                    @else <p class="text-muted small px-3 pb-2">Belum ada pengumpulan</p> @endif
                </div>
                @endforeach
                @endforeach
            </div>
        </div>
        @endif

        {{-- PENILAIAN --}}
        @if($komponenList->count())
        <div class="card shadow-sm mt-3">
            <div class="card-header"><h5><i class="fas fa-star mr-2"></i> Penilaian</h5></div>
            <div class="card-body p-0"><table class="table table-sm mb-0"><tbody>
                @foreach($komponenList as $k)
                @php $nilai = $penilaianData[$k->id]->nilai ?? null; @endphp
                <tr>
                    <td width="50"><span class="badge badge-secondary">{{ $k->bobot }}%</span></td>
                    <td>{{ $k->nama_komponen }} <small class="text-muted">({{ $k->kategori==='dpl'?'DPL':'LPPM' }})</small></td>
                    <td width="80"><strong>{{ $nilai !== null ? number_format($nilai,2) : '-' }}</strong></td>
                </tr>
                @endforeach
            </tbody></table></div>
        </div>
        @endif

    </div>

</section>
@endsection
