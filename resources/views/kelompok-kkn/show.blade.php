@extends('layouts.app')

@section('title', 'Detail Kelompok KKN')

@push('css')
<style>
    .group-nav {
        display: flex; justify-content: center; gap: 0; background: #fff;
        border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.06);
        overflow: hidden; margin-bottom: 24px; flex-wrap: wrap;
    }
    .group-nav a {
        padding: 14px 20px; text-align: center; font-size: 13px; font-weight: 600;
        color: #6c757d; border-bottom: 3px solid transparent; transition: .2s;
        text-decoration: none; white-space: nowrap; cursor: pointer;
    }
    .group-nav a:hover, .group-nav a.active { color: #6777ef; border-bottom-color: #6777ef; background: #f8f9ff; }
    .group-nav a i { margin-right: 6px; }
    [data-bs-theme="dark"] .group-nav { background: #1f2430; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
    [data-bs-theme="dark"] .group-nav a { color: #aab1c1; }
    [data-bs-theme="dark"] .group-nav a:hover, [data-bs-theme="dark"] .group-nav a.active { background: rgba(103,119,239,.1); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .proposal-doc {
        background: #fff; border-radius: 12px; padding: 48px 40px;
        box-shadow: 0 2px 16px rgba(0,0,0,.06); max-width: 860px; margin: 0 auto;
    }
    .proposal-doc-header { text-align: center; border-bottom: 2px solid #0f3460; padding-bottom: 24px; margin-bottom: 28px; }
    .proposal-doc-header h3 { font-size: 1.1rem; font-weight: 700; color: #0f3460; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .5px; }
    .proposal-doc-header h2 { font-size: 1.3rem; font-weight: 800; color: #1a1a2e; margin: 8px 0; }
    .proposal-doc-header .doc-meta { font-size: .85rem; color: #6c757d; text-align: center; }
    .proposal-doc-body h4 { font-size: 1rem; font-weight: 700; color: #0f3460; margin: 24px 0 10px; text-transform: uppercase; text-align: center; }
    .proposal-doc-body p { text-align: justify; line-height: 1.8; margin-bottom: 20px; font-size: .9rem; }
    .proposal-doc-body p.text-muted { text-align: center; }
    [data-bs-theme="dark"] .proposal-doc { background: #1f2430; }
    [data-bs-theme="dark"] .proposal-doc-header { border-bottom-color: #374151; }
    [data-bs-theme="dark"] .proposal-doc-body h4 { color: #a4b0f5; }
    [data-bs-theme="dark"] .proposal-doc-header h2 { color: #f1f3f8; }
    [data-bs-theme="dark"] .proposal-doc-header .doc-meta { color: #aab1c1; }
    [data-bs-theme="dark"] .bg-light { background-color: #2a2f3a !important; }
    [data-bs-theme="dark"] .table .bg-light td,
    [data-bs-theme="dark"] .table .bg-light th { background-color: #2a2f3a !important; }
    .logbook-toggle:checked + .custom-switch-indicator { background: #47c363; }
    .logbook-toggle + .custom-switch-indicator { background: #adb5bd; }
    .logbook-table img.lb-preview-img { max-width:180px; max-height:180px; object-fit:contain; }
</style>
@endpush

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
                                <span class="badge badge-primary">Draft</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- PROGRESS --}}
        @php $persentase = $kelompok_kkn->kuota > 0 ? ($kelompok_kkn->terisi / $kelompok_kkn->kuota) * 100 : 0; @endphp
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Kapasitas Kelompok</strong>
                    <strong>{{ $kelompok_kkn->terisi }}/{{ $kelompok_kkn->kuota }}</strong>
                </div>
                <div class="progress" style="height:20px;">
                    <div class="progress-bar {{ $kelompok_kkn->is_full ? 'bg-danger' : 'bg-primary' }}" style="width:{{ min($persentase,100) }}%">{{ round($persentase) }}%</div>
                </div>
            </div>
        </div>

        {{-- NAV TABS --}}
        <div class="group-nav mb-3">
            <a class="active" data-tab="admin-proposal"><i class="fas fa-file-alt mr-1"></i> Proposal</a>
            <a data-tab="admin-anggota"><i class="fas fa-users mr-1"></i> Anggota</a>
            <a data-tab="admin-status"><i class="fas fa-tasks mr-1"></i> Status</a>
            <a data-tab="admin-tugas"><i class="fas fa-upload mr-1"></i> Tugas</a>
            <a data-tab="admin-logbook"><i class="fas fa-book mr-1"></i> Log Book</a>
            <a data-tab="admin-penilaian"><i class="fas fa-star mr-1"></i> Penilaian</a>
            <a data-tab="admin-laporan"><i class="fas fa-file-upload mr-1"></i> Laporan</a>
        </div>

        {{-- TAB: PROPOSAL --}}
        <div class="tab-content active" id="tab-admin-proposal">
            @if($proposal)
            <div class="proposal-doc">
                <div class="proposal-doc-header">
                    <h3>Proposal Program Kerja KKN</h3>
                    <h2>{{ $kelompok_kkn->nama_kelompok }}</h2>
                    <div class="doc-meta">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ $kelompok_kkn->desaGelombang->desa->nama_desa ?? '-' }},
                        {{ $kelompok_kkn->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }},
                        {{ $kelompok_kkn->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}
                        &nbsp;&middot;&nbsp;
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ \Carbon\Carbon::parse($kelompok_kkn->desaGelombang->gelombang->tgl_mulai ?? now())->format('d M Y') }}
                        &mdash;
                        {{ \Carbon\Carbon::parse($kelompok_kkn->desaGelombang->gelombang->tgl_akhir ?? now())->format('d M Y') }}
                    </div>
                    <div class="mt-2"><span class="badge badge-{{ $proposal->status==='disetujui'?'success':($proposal->status==='ditolak'?'danger':'info') }}">{{ $proposal->status }}</span></div>
                </div>
                <div class="proposal-doc-body">
                    @foreach(['pendahuluan'=>'Pendahuluan','tujuan'=>'Tujuan','manfaat'=>'Manfaat','hasil_observasi'=>'Hasil Observasi','rancangan_program'=>'Rancangan Program','solusi_ide'=>'Solusi & Ide'] as $f=>$l)
                    <h4>{{ $l }}</h4>
                    @php $content = $proposal->$f; $isEmpty = !$content || trim(strip_tags($content)) === ''; @endphp
                    @if($isEmpty)<p class="text-muted text-center">Belum ada {{ $l }}</p>@else<p>{!! $content !!}</p>@endif
                    @endforeach
                </div>
            </div>
            @else
            <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">📄</span><h5>Belum Ada Proposal</h5><p class="text-muted">Ketua kelompok belum membuat proposal.</p></div></div>
            @endif
        </div>

        {{-- TAB: ANGGOTA --}}
        <div class="tab-content" id="tab-admin-anggota">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center"><h4 class="mb-0 mr-3">Anggota Kelompok</h4><span class="badge badge-primary">{{ $kelompok_kkn->terisi }} Anggota</span></div>
                    @if(!$kelompok_kkn->is_full)<a href="{{ route('kelompok-kkn.anggota.create', $kelompok_kkn->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-user-plus mr-1"></i> Tambah Anggota</a>@endif
                </div>
                <div class="card-body"><div class="form-group mb-3"><input type="text" class="form-control" id="anggotaSearch" placeholder="Cari anggota..."></div></div>
                <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0" id="anggotaTable">
                    <thead><tr><th width="40">No</th><th>Nama</th><th>NPM</th><th>JK</th><th>No. HP</th><th>Fakultas</th><th>Prodi</th><th width="170">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($kelompok_kkn->pesertaKkn as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->mahasiswa?->user?->name ?? '-' }}
                                @if($item->id === $kelompok_kkn->ketua_peserta_id)<span class="badge badge-warning ml-1" style="font-size:10px;border-radius:8px;">Ketua</span>@endif</td>
                            <td>{{ $item->mahasiswa?->npm ?? '-' }}</td>
                            <td>{{ $item->mahasiswa?->jenis_kelamin ?? '-' }}</td>
                            <td>{{ $item->mahasiswa?->no_hp ?? '-' }}</td>
                            <td>{{ $item->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-' }}</td>
                            <td>{{ $item->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                            <td><div class="d-flex gap-1">
                                @if($item->id !== $kelompok_kkn->ketua_peserta_id)
                                <form action="{{ route('kelompok-kkn.ketua', ['kelompok_kkn'=>$kelompok_kkn->id,'peserta'=>$item->id]) }}" method="POST" onsubmit="return confirm('Jadikan ketua?')">@csrf @method('PUT')<button type="submit" class="btn btn-warning btn-sm" title="Jadikan Ketua"><i class="fas fa-crown"></i></button></form>
                                @endif
                                <form action="{{ route('kelompok-kkn.anggota.destroy', ['kelompok_kkn'=>$kelompok_kkn->id,'peserta'=>$item->id]) }}" method="POST" onsubmit="return confirm('Keluarkan?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
                            </div></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada anggota.</td></tr>
                        @endforelse
                    </tbody>
                </table></div></div>
            </div>
        </div>

        {{-- TAB: STATUS --}}
        <div class="tab-content" id="tab-admin-status">
            <div class="card"><div class="card-body">
                <div class="d-flex justify-content-center flex-wrap gap-1 mb-3">
                    @foreach($statusStages as $i => $s)
                    <div class="text-center px-2" style="min-width:85px;">
                        <div style="width:30px;height:30px;border-radius:50%;margin:0 auto 3px;background:{{ $i <= (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#e0e0e0' }};color:{{ $i <= (int)$kelompok_kkn->status_tahap ? '#fff' : '#999' }};font-weight:800;font-size:12px;line-height:30px;">{{ $i }}</div>
                        <small style="font-size:9px;color:{{ $i === (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#adb5bd' }};">{{ $s['nama'] }}</small>
                    </div>
                    @if($i < 3)<div style="width:20px;height:2px;background:{{ $i < (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#e0e0e0' }};margin-top:14px;flex-shrink:0;"></div>@endif
                    @endforeach
                </div>
                <div class="alert alert-primary text-center mb-3"><strong>Tahap Saat Ini: {{ $statusCurrent['nama'] }}</strong></div>
                <form action="{{ route('kelompok.status.change', $kelompok_kkn->id) }}" method="POST" class="form-inline gap-2 justify-content-center">
                    @csrf
                    <select name="stage" class="form-control form-control-sm"><option value="">Ubah Status</option>
                    @foreach($statusStages as $i => $s)<option value="{{ $i }}" {{ $i===(int)$kelompok_kkn->status_tahap?'disabled':'' }}>{{ $i }} - {{ $s['nama'] }}</option>@endforeach</select>
                    <input name="keterangan" class="form-control form-control-sm" placeholder="Keterangan">
                    <button class="btn btn-primary btn-sm" onclick="return confirm('Ubah?')">Simpan</button>
                </form>
            </div></div>
        </div>

        {{-- TAB: TUGAS --}}
        <div class="tab-content" id="tab-admin-tugas">
            @if($tugasList->count())
            @php
                $wajibTasks = collect(); $otherTasks = collect();
                foreach ($tugasList as $kat => $items) {
                    $wajibTasks[$kat] = $items->filter(fn($t) => $t->is_wajib);
                    $otherTasks[$kat] = $items->filter(fn($t) => !$t->is_wajib);
                }
                $katLabels = ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Lain','laporan'=>'Laporan'];
            @endphp

            @if($wajibTasks->sum(fn($g) => $g->count()) > 0)
             <div class="card mb-3 border-danger">
                <div class="card-header bg-danger text-white py-2"><h5 class="mb-0"><i class="fas fa-star mr-2"></i>Tugas Wajib</h5></div>
                <div class="card-body p-0">
                    @foreach($wajibTasks as $kat => $items)
                    @if($items->count())
                    <div class="border-bottom"><div class="px-3 py-2 text-white" style="background:#6777ef;"><small class="font-weight-bold">{{ $katLabels[$kat] ?? $kat }}</small></div>
                    @foreach($items as $t)
                    <div class="border-bottom p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $t->nama_tugas }}</strong>
                            <span class="badge badge-danger">Wajib</span>
                        </div>
                        @if($t->submissions->count())
                        <table class="table table-striped table-sm mt-2 mb-0">
                            <thead style="background:#2D3A8A;"><tr><th class="text-white text-center" width="40">#</th><th class="text-white">Judul</th><th class="text-white" width="160">Oleh</th>                <th class="text-white text-center" width="100">Status</th><th class="text-white text-center" width="50">Aksi</th></tr></thead>
                            <tbody>
                                @foreach($t->submissions as $i => $s)
                                <tr>
                                    <td class="text-center">{{ $i+1 }}</td>
                                    <td>{{ $s->judul }}</td>
                                    <td><small>{{ $s->pesertaKkn->mahasiswa->user->name ?? '-' }}</small></td>
                                    <td class="text-center">
                                        @if($s->status==='diterima')<span class="badge badge-success">Diterima</span>
                                        @elseif($s->status==='ditolak')<span class="badge badge-danger">Ditolak</span>
                                        @elseif($s->status==='revisi')<span class="badge badge-warning">Revisi</span>
@else<span class="badge badge-info">Menunggu</span>@endif
                    </td>
                    <td class="text-center">
                                        <button class="btn btn-info btn-sm" onclick='showSubmission(@json($s->id), @json($s->judul), @json($s->pesertaKkn->mahasiswa->user->name ?? "-"), @json($s->status), @json($s->komentar_dpl ?? ""), @json($s->file_path ? storage_url($s->file_path) : ""), @json($s->file_name ?? ""))'><i class="fas fa-eye"></i> Show</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else <p class="text-muted small px-2 py-2">Belum ada pengumpulan</p> @endif
                    </div>
                    @endforeach
                    </div>@endif
                    @endforeach
                </div>
            </div>
            @endif

            @if($otherTasks->sum(fn($g) => $g->count()) > 0)
            <div class="card mb-3">
                <div class="card-header py-2"><h5 class="mb-0">Tugas Lainnya</h5></div>
                <div class="card-body p-0">
                    @foreach($otherTasks as $kat => $items)
                    @if($items->count())
                    <div class="border-bottom"><div class="px-3 py-2 text-white" style="background:#6777ef;"><small class="font-weight-bold">{{ $katLabels[$kat] ?? $kat }}</small></div>
                    @foreach($items as $t)
                    <div class="border-bottom p-2">
                        <strong>{{ $t->nama_tugas }}</strong>
                        @if($t->submissions->count())
                        <table class="table table-striped table-sm mt-2 mb-0">
                            <thead style="background:#2D3A8A;"><tr><th class="text-white text-center" width="40">#</th><th class="text-white">Judul</th><th class="text-white" width="160">Oleh</th>                <th class="text-white text-center" width="100">Status</th><th class="text-white text-center" width="50">Aksi</th></tr></thead>
                            <tbody>
                                @foreach($t->submissions as $i => $s)
                                <tr>
                                    <td class="text-center">{{ $i+1 }}</td>
                                    <td>{{ $s->judul }}</td>
                                    <td><small>{{ $s->pesertaKkn->mahasiswa->user->name ?? '-' }}</small></td>
                                    <td class="text-center">@if($s->status==='diterima')<span class="badge badge-success">Diterima</span>@elseif($s->status==='ditolak')<span class="badge badge-danger">Ditolak</span>@elseif($s->status==='revisi')<span class="badge badge-warning">Revisi</span>@else<span class="badge badge-info">Menunggu</span>@endif</td>
                                    <td class="text-center"><button class="btn btn-info btn-sm" onclick='showSubmission(@json($s->id), @json($s->judul), @json($s->pesertaKkn->mahasiswa->user->name ?? "-"), @json($s->status), @json($s->komentar_dpl ?? ""), @json($s->file_path ? storage_url($s->file_path) : ""), @json($s->file_name ?? ""))'><i class="fas fa-eye"></i> Show</button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else <p class="text-muted small px-2 py-2">Belum ada pengumpulan</p> @endif
                    </div>
                    @endforeach
                    </div>@endif
                    @endforeach
                </div>
            </div>
            @endif

            @if($wajibTasks->sum(fn($g) => $g->count()) == 0 && $otherTasks->sum(fn($g) => $g->count()) == 0)
            <div class="card"><div class="card-body text-center py-4 text-muted">Belum ada tugas.</div></div>
            @endif
            @else <div class="card"><div class="card-body text-center py-4 text-muted">Belum ada tugas.</div></div> @endif
        </div>

        {{-- TAB: LOGBOOK --}}
        <div class="tab-content" id="tab-admin-logbook">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0 text-default"><i class="fas fa-book mr-2"></i>Log Book</h4>
                </div>
            </div>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2">
                    <div class="row justify-content-between align-items-end">
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold">Cari Berdasarkan</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control admin-logbook-search" placeholder="Cari judul, deskripsi, atau tanggal...">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Pilih Anggota</label>
                                    <select id="logbook-member-select" class="form-control form-control-sm" onchange="adminFilterLogbook()" style="min-width:160px;">
                                        <option value="">Semua Anggota</option>
                                        @foreach($kelompok_kkn->pesertaKkn as $p)
                                        <option value="lb-{{ $p->id }}">{{ $p->mahasiswa->user->name ?? 'Unknown' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Tampilkan</label>
                                    <select class="form-control form-control-sm admin-logbook-perpage" style="width:80px;">
                                        <option value="10">10</option>
                                        <option value="25" selected>25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Urutkan</label>
                                    <select class="form-control form-control-sm admin-logbook-sortby" style="width:110px;">
                                        <option value="tanggal">Tanggal</option>
                                        <option value="judul">Judul</option>
                                        <option value="deskripsi">Deskripsi</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label class="small font-weight-bold">Tipe</label>
                                    <select class="form-control form-control-sm admin-logbook-order" style="width:90px;">
                                        <option value="desc">DESC</option>
                                        <option value="asc">ASC</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <ul class="nav nav-tabs" style="cursor:pointer;">
                        <li class="nav-item">
                            <label class="custom-switch nav-link" style="cursor:pointer;margin-bottom:0;">
                                                <input type="checkbox" class="custom-switch-input logbook-toggle admin-logbook-toggle-doc" id="adminLogbookToggleDoc" checked>
                                <span class="custom-switch-indicator"></span>
                                <span class="custom-switch-description ml-2" style="font-size:13px;">Tampilkan Dokumen Langsung</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            @foreach($logbookData as $pesertaId => $entries)
            @php $member = $entries->first()->pesertaKkn->mahasiswa->user; $v = $entries->where('is_validated',true)->count(); @endphp
            <div class="card border-0 shadow-sm mb-3 logbook-member-card" id="card-lb-{{ $pesertaId }}" style="display:none;">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <strong>{{ $member->name ?? 'Unknown' }} <small class="text-muted ml-2">{{ $entries->count() }} entri</small></strong>
                    <div>
                        <span class="badge badge-{{ $v>=20?'success':'warning' }} mr-2">{{ $v }}/{{ $entries->count() }}</span>
                        <form action="{{ route('kelompok.logbook.validateAll') }}" method="POST" class="d-inline">@csrf
                            <input type="hidden" name="peserta_id" value="{{ $pesertaId }}">
                            <button class="btn btn-sm btn-warning" onclick="return confirm('Validasi semua?')">Validasi Semua</button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive rounded-bottom">
                        <table class="table table-bordered table-hover align-middle mb-0 logbook-table" data-peserta="{{ $pesertaId }}" style="border-collapse:collapse;">
                            <thead style="background:#2D3A8A;">
                                <tr>
                                    <th class="text-white py-2 text-center" width="50">#</th>
                                    <th class="text-white py-2" width="250">Judul</th>
                                    <th class="text-white py-2">Deskripsi</th>
                                    <th class="text-white py-2 text-center" style="width:20%;">Berkas</th>
                                    <th class="text-white py-2 text-center" width="100">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $i => $lb)
                                <tr class="logbook-row" data-judul="{{ strtolower($lb->judul) }}" data-deskripsi="{{ strtolower($lb->deskripsi) }}" data-tanggal="{{ $lb->tanggal->format('Ymd') }}">
                                    <td class="text-center text-muted small">{{ $i+1 }}</td>
                                    <td>
                                        <small class="text-muted d-block">{{ $lb->tanggal->format('d F Y') }}</small>
                                        <strong>{{ $lb->judul }}</strong>
                                    </td>
                                    <td class="text-justify"><small>{{ $lb->deskripsi }}</small></td>
                                    <td class="text-center">
                                        @if($lb->file_path)
                                        <div class="logbook-download" style="display:none;">
                                            <a href="{{ storage_url($lb->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i> Unduh</a>
                                        </div>
                                        <div class="logbook-preview" style="display:inline;">
                                            @php $ext = pathinfo($lb->file_path, PATHINFO_EXTENSION); @endphp
                                            @if(in_array($ext, ['jpg','jpeg','png','gif']))
                                            <img src="{{ storage_url($lb->file_path) }}" class="lb-preview-img my-2 rounded shadow-sm" onclick="window.open('{{ storage_url($lb->file_path) }}')" title="Klik untuk lihat penuh">
                                            @elseif($ext === 'pdf')
                                            <a href="{{ storage_url($lb->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf fa-lg mr-1"></i>PDF</a>
                                            @else
                                            <a href="{{ storage_url($lb->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file mr-1"></i>File</a>
                                            @endif
                                        </div>
                                        @else <span class="text-muted">-</span> @endif
                                    </td>
                                    <td class="text-center">@if($lb->is_validated)<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i></span>@else<span class="badge badge-warning">Belum</span>@endif</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="p-3 bg-light">
                            <small class="text-muted logbook-info">Menampilkan <span class="lb-showing">0</span> dari <span class="lb-total">0</span> entri</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @if($logbookData->isEmpty())
            <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><span style="font-size:48px;">📖</span><h5>Belum Ada Log Book</h5><p class="text-muted">Anggota kelompok belum membuat catatan harian.</p></div></div>
            @endif
        </div>

        {{-- TAB: PENILAIAN --}}
        <div class="tab-content" id="tab-admin-penilaian">
            @if($komponenList->count())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h4 class="mb-0">Penilaian Kelompok</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0" style="border-collapse:collapse;">
                            <thead>
                                <tr style="background:#2D3A8A;">
                                    <th class="text-white py-3" width="60">#</th>
                                    <th class="text-white py-3">Komponen Penilaian</th>
                                    <th class="text-white text-center py-3" width="80">Bobot</th>
                                    <th class="text-white text-center py-3" width="100">Nilai</th>
                                    <th class="text-white text-center py-3" width="170">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DPL SECTION --}}
                                <tr class="bg-light">
                                    <td colspan="5" class="py-2"><strong><i class="fas fa-user-tie mr-2"></i>Dosen Pembimbing Lapangan (DPL)</strong></td>
                                </tr>
                                @foreach($komponenList->where('kategori','dpl') as $k)
                                @php $nilai = $penilaianData[$k->id]->nilai ?? null; @endphp
                                <tr>
                                    <td class="text-center">{{ $k->urutan }}</td>
                                    <td><strong>{{ $k->nama_komponen }}</strong><br><small class="text-muted">{{ $k->deskripsi }}</small></td>
                                    <td class="text-center"><span class="badge badge-primary">{{ $k->bobot }}%</span></td>
                                    <td class="text-center">{{ $nilai !== null ? number_format($nilai,2) : '-' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('kelompok.penilaian.input') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok_kkn->id }}">
                                            <input type="hidden" name="komponen_id" value="{{ $k->id }}">
                                            <div class="d-flex justify-content-center">
                                                <input type="number" name="nilai" class="form-control form-control-sm text-center rounded-right-0" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width:90px;">
                                                <button class="btn btn-sm btn-primary rounded-left-0"><i class="fas fa-save"></i></button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                <tr style="background:#eef1f8;">
                                    <td colspan="4" class="text-right font-weight-bold py-2">Nilai Akhir dari Dosen Pembimbing Lapangan</td>
                                    <td class="text-center font-weight-bold py-2">{{ $dplFinal ? number_format($dplFinal, 2) : '-' }}</td>
                                </tr>

                                {{-- LPPM SECTION --}}
                                <tr class="bg-light">
                                    <td colspan="5" class="py-2"><strong><i class="fas fa-building mr-2"></i>LPPM UBT</strong></td>
                                </tr>
                                @foreach($komponenList->where('kategori','lppm') as $k)
                                @php $nilai = $penilaianData[$k->id]->nilai ?? null; @endphp
                                <tr>
                                    <td class="text-center">{{ $k->urutan }}</td>
                                    <td><strong>{{ $k->nama_komponen }}</strong><br><small class="text-muted">{{ $k->deskripsi }}</small></td>
                                    <td class="text-center"><span class="badge badge-primary">{{ $k->bobot }}%</span></td>
                                    <td class="text-center">{{ $nilai !== null ? number_format($nilai,2) : '-' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('kelompok.penilaian.input') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok_kkn->id }}">
                                            <input type="hidden" name="komponen_id" value="{{ $k->id }}">
                                            <div class="d-flex justify-content-center">
                                                <input type="number" name="nilai" class="form-control form-control-sm text-center rounded-right-0" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width:90px;">
                                                <button class="btn btn-sm btn-primary rounded-left-0"><i class="fas fa-save"></i></button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                                <tr style="background:#eef1f8;">
                                    <td colspan="4" class="text-right font-weight-bold py-2">Nilai Akhir dari LPPM UBT</td>
                                    <td class="text-center font-weight-bold py-2">{{ $lppmFinal ? number_format($lppmFinal, 2) : '-' }}</td>
                                </tr>

                                {{-- FINAL SCORE --}}
                                <tr style="background:#2D3A8A;">
                                    <td colspan="4" class="text-right font-weight-bold text-white py-3" style="font-size:1.1rem;">Nilai Akhir</td>
                                    <td class="text-center font-weight-bold text-white py-3" style="font-size:1.1rem;">{{ $finalScore ? number_format($finalScore, 2) : '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">⭐</span><h5>Belum Ada Komponen Penilaian</h5><p class="text-muted">Jalankan seeder PenilaianKomponenSeeder terlebih dahulu.</p></div></div>
            @endif
        </div>

        {{-- TAB: LAPORAN --}}
        <div class="tab-content" id="tab-admin-laporan">
            @php $jenisLabels = ['monev'=>'Laporan Monev','artikel'=>'Artikel','haki'=>'HAKI']; @endphp
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-file-upload mr-2"></i>Upload Laporan</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('kelompok-kkn.laporan.store', $kelompok_kkn->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Jenis</label>
                                    <select name="jenis" class="form-control" required>
                                        <option value="monev">Laporan Monev</option>
                                        <option value="artikel">Artikel</option>
                                        <option value="haki">HAKI</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Judul</label>
                                    <input name="judul" class="form-control" placeholder="Judul laporan..." required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">File</label>
                                    <input type="file" name="file" class="form-control-file mt-2">
                                    <small class="text-muted">PDF, DOC, JPG, PNG — Maks 10MB</small>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary mb-3 w-100"><i class="fas fa-upload mr-1"></i> Upload</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi (opsional)..."></textarea>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h4 class="mb-0"><i class="fas fa-list mr-2"></i>Daftar Laporan</h4></div>
                <div class="card-body p-0">
                    @if(isset($laporans) && $laporans->count())
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead><tr><th class="text-center" width="50">#</th><th width="80">Jenis</th><th>Judul</th><th width="100">Berkas</th><th width="100">Tanggal</th><th width="60">Aksi</th></tr></thead>
                            <tbody>
                                @foreach($laporans->sortKeys() as $jenis => $items)
                                    @foreach($items as $i => $l)
                                    <tr>
                                        <td class="text-center">{{ $loop->parent->iteration }}.{{ $i+1 }}</td>
                                        <td><span class="badge badge-{{ $jenis==='monev'?'primary':($jenis==='artikel'?'info':'success') }}">{{ $jenisLabels[$jenis] }}</span></td>
                                        <td><strong>{{ $l->judul }}</strong></td>
                                        <td>@if($l->file_path)<a href="{{ storage_url($l->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>@else - @endif</td>
                                        <td><small>{{ $l->created_at->format('d M Y') }}</small></td>
                                        <td>
                                            <form action="{{ route('kelompok-kkn.laporan.destroy', [$kelompok_kkn->id, $l->id]) }}" method="POST" onsubmit="return confirm('Hapus?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted"><span style="font-size:48px;">📄</span><h5>Belum Ada Laporan</h5></div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</section>

        {{-- MODAL SUBMISSION --}}
        <div class="modal fade" id="submissionModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background:#2D3A8A;color:#fff;">
                        <h5 class="modal-title">Detail Pengumpulan</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered">
                            <tr><th width="140">Judul</th><td id="mod-judul">-</td></tr>
                            <tr><th>Oleh</th><td id="mod-oleh">-</td></tr>
                            <tr><th>Status</th><td id="mod-status">-</td></tr>
                            <tr><th>Komentar DPL</th><td id="mod-komentar">-</td></tr>
                            <tr><th>Berkas</th><td id="mod-berkas">-</td></tr>
                        </table>
                        <hr>
                        <div id="mod-review">
                            <form id="mod-review-form" method="POST" action="">
                                @csrf
                                <input name="komentar_dpl" class="form-control form-control-sm mb-2" placeholder="Komentar...">
                                <button name="status" value="diterima" class="btn btn-success btn-sm mr-1"><i class="fas fa-check mr-1"></i> Terima</button>
                                <button name="status" value="ditolak" class="btn btn-danger btn-sm"><i class="fas fa-times mr-1"></i> Tolak</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.group-nav a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.group-nav a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + this.dataset.tab).classList.add('active');
        });
    });
    (function() {
        var p = new URLSearchParams(window.location.search);
        var tab = p.get('tab');
        if (tab) {
            var link = document.querySelector('.group-nav a[data-tab="' + tab + '"]');
            if (link) link.click();
        }
    })();
    function adminFilterLogbook() {
        var val = document.getElementById('logbook-member-select').value;
        document.querySelectorAll('.logbook-member-card').forEach(function(c) {
            c.style.display = val ? (c.id === 'card-' + val ? '' : 'none') : '';
        });
        applyAdminLogbookFilters();
    }
    function applyAdminLogbookFilters() {
        var search = (document.querySelector('.admin-logbook-search')?.value || '').toLowerCase();
        var perPage = parseInt(document.querySelector('.admin-logbook-perpage')?.value || 25);
        var sortBy = document.querySelector('.admin-logbook-sortby')?.value || 'tanggal';
        var order = document.querySelector('.admin-logbook-order')?.value || 'desc';
        var showDoc = document.querySelector('.admin-logbook-toggle-doc')?.checked || false;

        document.querySelectorAll('.logbook-member-card').forEach(function(card) {
            if (card.style.display === 'none') return;
            var tbody = card.querySelector('tbody');
            if (!tbody) return;
            var rows = Array.from(tbody.querySelectorAll('.logbook-row'));
            var info = card.querySelector('.logbook-info');
            var showingEl = card.querySelector('.lb-showing');
            var totalEl = card.querySelector('.lb-total');

            var filtered = rows.filter(function(r) {
                if (!search) return true;
                var text = (r.dataset.judul || '') + ' ' + (r.dataset.deskripsi || '') + ' ' + (r.dataset.tanggal || '');
                return text.includes(search);
            });

            var total = filtered.length;
            filtered.sort(function(a, b) {
                var valA = a.dataset[sortBy] || '', valB = b.dataset[sortBy] || '';
                if (order === 'desc') return valA < valB ? 1 : -1;
                return valA > valB ? 1 : -1;
            });
            var shown = filtered.slice(0, perPage);

            rows.forEach(function(r) {
                var download = r.querySelector('.logbook-download');
                var preview = r.querySelector('.logbook-preview');
                if (download) download.style.display = showDoc ? 'none' : '';
                if (preview) preview.style.display = showDoc ? '' : 'none';
            });

            rows.forEach(function(r) { r.style.display = 'none'; });
            shown.forEach(function(r) { r.style.display = ''; });

            if (showingEl) showingEl.textContent = Math.min(perPage, total);
            if (totalEl) totalEl.textContent = total;
            if (info) info.style.display = total === 0 ? 'none' : '';
        });
    }
    document.querySelector('.admin-logbook-search')?.addEventListener('keyup', applyAdminLogbookFilters);
    document.querySelector('.admin-logbook-perpage')?.addEventListener('change', applyAdminLogbookFilters);
    document.querySelector('.admin-logbook-sortby')?.addEventListener('change', applyAdminLogbookFilters);
    document.querySelector('.admin-logbook-order')?.addEventListener('change', applyAdminLogbookFilters);
    document.querySelector('.admin-logbook-toggle-doc')?.addEventListener('change', applyAdminLogbookFilters);
    setTimeout(applyAdminLogbookFilters, 100);
    document.getElementById('anggotaSearch')?.addEventListener('keyup', function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#anggotaTable tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
    function showSubmission(id, judul, oleh, status, komentar, fileUrl, fileName) {
        document.getElementById('mod-judul').textContent = judul;
        document.getElementById('mod-oleh').textContent = oleh;
        document.getElementById('mod-status').innerHTML = status === 'diterima' ? '<span class="badge badge-success">Diterima</span>' : status === 'ditolak' ? '<span class="badge badge-danger">Ditolak</span>' : status === 'revisi' ? '<span class="badge badge-warning">Revisi</span>' : '<span class="badge badge-info">Menunggu</span>';
        document.getElementById('mod-komentar').textContent = komentar || '-';
        if (fileUrl) {
            document.getElementById('mod-berkas').innerHTML = '<a href="'+fileUrl+'" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-download mr-1"></i>' + fileName + '</a>';
        } else { document.getElementById('mod-berkas').textContent = '-'; }
        document.getElementById('mod-review-form').action = "/kelompok/tugas/submission/" + id + "/review";
        document.getElementById('mod-review').style.display = (status === 'diterima' || status === 'ditolak') ? 'none' : '';
        $('#submissionModal').modal('show');
    }
</script>
@endpush
