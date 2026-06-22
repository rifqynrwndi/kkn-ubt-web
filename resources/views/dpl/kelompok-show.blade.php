@extends('layouts.app')

@section('title', 'Detail Kelompok — ' . $kelompok->nama_kelompok)
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
    .proposal-doc-body h4 { font-size: 1rem; font-weight: 700; color: #0f3460; margin: 24px 0 10px; text-transform: uppercase; text-align: center; letter-spacing: .3px; }
    .proposal-doc-body p { text-align: justify; line-height: 1.8; margin-bottom: 20px; font-size: .9rem; }
    .proposal-doc-body p.text-muted { text-align: center; }
    [data-bs-theme="dark"] .proposal-doc { background: #1f2430; }
    [data-bs-theme="dark"] .proposal-doc-header { border-bottom-color: #374151; }
    [data-bs-theme="dark"] .proposal-doc-header h3, [data-bs-theme="dark"] .proposal-doc-body h4 { color: #a4b0f5; }
    [data-bs-theme="dark"] .proposal-doc-header h2 { color: #f1f3f8; }
    [data-bs-theme="dark"] .proposal-doc-header .doc-meta { color: #aab1c1; }
    [data-bs-theme="dark"] .bg-light { background-color: #2a2f3a !important; }
    [data-bs-theme="dark"] .table .bg-light td,
    [data-bs-theme="dark"] .table .bg-light th { background-color: #2a2f3a !important; }
    [data-bs-theme="dark"] .text-dark { color: #e1e5eb !important; }
    .task-cat-header { background: #e9ecef; }
    [data-bs-theme="dark"] .task-cat-header { background: #2a2f3a !important; }
    .logbook-toggle:checked + .custom-switch-indicator { background: #47c363; }
    .logbook-toggle + .custom-switch-indicator { background: #adb5bd; }
    .logbook-table img.lb-preview-img { max-width:180px; max-height:180px; object-fit:contain; }
</style>
@endpush
@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $kelompok->nama_kelompok }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('dpl.kelompok.index') }}">Kelompok Binaan</a></div>
            <div class="breadcrumb-item active">Detail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header"><h4>Informasi Kelompok</h4></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th width="140">Nama Kelompok</th><td>{{ $kelompok->nama_kelompok }}</td></tr>
                            <tr><th>Kode</th><td>{{ $kelompok->kode_kelompok }}</td></tr>
                            <tr><th>Desa</th><td>{{ $kelompok->desaGelombang->desa->nama_desa ?? '-' }}</td></tr>
                            <tr><th>Kecamatan</th><td>{{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}</td></tr>
                            <tr><th>Kabupaten</th><td>{{ $kelompok->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}</td></tr>
                            <tr><th>Kuota</th><td>{{ $kelompok->pesertaKkn->count() }} / {{ $kelompok->kuota }}</td></tr>
                            <tr><th>Ketua</th><td>{{ $kelompok->ketua?->mahasiswa?->user?->name ?? 'Belum ditentukan' }}</td></tr>
                            <tr><th>Status</th><td><span class="badge badge-{{ $kelompok->status === 'penuh' ? 'danger' : 'success' }}">{{ $kelompok->status }}</span></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Anggota Kelompok</h4>
                        <span class="badge badge-primary">{{ $kelompok->pesertaKkn->count() }} orang</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Nama</th>
                                        <th>NPM</th>
                                        <th>JK</th>
                                        <th>No. HP</th>
                                        <th>Prodi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelompok->pesertaKkn as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('dpl.mahasiswa.show', $p->id) }}">
                                                {{ $p->mahasiswa?->user?->name ?? '-' }}
                                            </a>
                                            @if($p->id === $kelompok->ketua_peserta_id)
                                                <span class="badge badge-warning ml-1">Ketua</span>
                                            @endif
                                        </td>
                                        <td>{{ $p->mahasiswa?->npm ?? '-' }}</td>
                                        <td>{{ $p->mahasiswa?->jenis_kelamin ?? '-' }}</td>
                                        <td>{{ $p->mahasiswa?->no_hp ?? '-' }}</td>
                                        <td>{{ $p->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                                        <td>
                                            @if($p->status_pendaftaran === 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-warning">{{ $p->status_pendaftaran }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Belum ada anggota.
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

        {{-- POST-WAR MODULES --}}
        <div class="row mt-3">
            <div class="col-12">
                <div class="group-nav">
                    <a class="active" data-tab="status"><i class="fas fa-tasks"></i> Status</a>
                    <a data-tab="anggota"><i class="fas fa-users"></i> Anggota</a>
                    <a data-tab="proposal"><i class="fas fa-file-alt"></i> Proposal</a>
                    <a data-tab="tugas"><i class="fas fa-upload"></i> Tugas</a>
                    <a data-tab="logbook"><i class="fas fa-book"></i> Log Book</a>
                    <a data-tab="penilaian"><i class="fas fa-star"></i> Penilaian</a>
                </div>

                <div class="tab-content active" id="tab-status">
                        <div class="card"><div class="card-body">
                            <div class="d-flex justify-content-center flex-wrap gap-1 mb-3">
                                @foreach($statusStages as $i => $s)
                                <div class="text-center px-2" style="min-width:85px;">
                                    <div style="width:30px;height:30px;border-radius:50%;margin:0 auto 3px;background:{{ $i <= (int)$kelompok->status_tahap ? '#6777ef' : '#e0e0e0' }};color:{{ $i <= (int)$kelompok->status_tahap ? '#fff' : '#999' }};font-weight:800;font-size:12px;line-height:30px;">{{ $i }}</div>
                                    <small style="font-size:9px;color:{{ $i === (int)$kelompok->status_tahap ? '#6777ef' : '#adb5bd' }};">{{ $s['nama'] }}</small>
                                </div>
                                @if($i < 3)<div style="width:20px;height:2px;background:{{ $i < (int)$kelompok->status_tahap ? '#6777ef' : '#e0e0e0' }};margin-top:14px;flex-shrink:0;"></div>@endif
                                @endforeach
                            </div>
                            <form action="{{ route('kelompok.status.change', $kelompok->id) }}" method="POST" class="form-inline gap-2 justify-content-center">
                                @csrf
                                <select name="stage" class="form-control form-control-sm"><option value="">Ubah Status</option>
                                @foreach($statusStages as $i => $s)<option value="{{ $i }}" {{ $i===(int)$kelompok->status_tahap?'disabled':'' }}>{{ $i }} - {{ $s['nama'] }}</option>@endforeach
                                </select>
                                <input name="keterangan" class="form-control form-control-sm" placeholder="Keterangan">
                                <button class="btn btn-primary btn-sm" onclick="return confirm('Ubah?')">Simpan</button>
                            </form>
                        </div></div>
                    </div>
                    {{-- TAB: ANGGOTA --}}
                    <div class="tab-content" id="tab-anggota">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4>Anggota Kelompok</h4>
                                <span class="badge badge-primary">{{ $kelompok->pesertaKkn->count() }} orang</span>
                            </div>
                            <div class="card-body">
                                <div class="form-group mb-3">
                                    <input type="text" class="form-control" id="anggotaSearch" placeholder="Cari anggota...">
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="anggotaTable">
                                        <thead><tr><th>No</th><th>Nama / NPM</th><th>JK</th><th>No. HP</th><th>Prodi</th><th>Status</th></tr></thead>
                                        <tbody>
                                            @forelse($kelompok->pesertaKkn as $index => $p)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $p->mahasiswa?->user?->name ?? '-' }}</strong>
                                                    @if($p->id === $kelompok->ketua_peserta_id)
                                                        <span class="badge badge-warning ml-1">Ketua</span>
                                                    @endif
                                                    <br><small class="text-muted">{{ $p->mahasiswa?->npm ?? '-' }}</small>
                                                </td>
                                                <td>{{ $p->mahasiswa?->jenis_kelamin ?? '-' }}</td>
                                                <td>{{ $p->mahasiswa?->no_hp ?? '-' }}</td>
                                                <td><small>{{ $p->mahasiswa?->prodi?->nama_prodi ?? '-' }}</small></td>
                                                <td>
                                                    @if($p->status_pendaftaran === 'approved')
                                                        <span class="badge badge-success">Approved</span>
                                                    @else
                                                        <span class="badge badge-warning">{{ $p->status_pendaftaran }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada anggota.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB: PROPOSAL --}}
                    <div class="tab-content" id="tab-proposal">
                        @if($proposal)
                        <div class="alert alert-{{ $proposal->status==='disetujui'?'success':($proposal->status==='ditolak'?'danger':'info') }} mb-3">
                            <strong>Status:</strong> {{ $proposal->status === 'disetujui' ? 'Disetujui' : ($proposal->status === 'ditolak' ? 'Ditolak' : 'Menunggu Review') }}
                            @if($proposal->status === 'ditolak' && $proposal->komentar_dpl)
                                <br><small><strong>Komentar:</strong> {{ $proposal->komentar_dpl }}</small>
                            @endif
                        </div>
                        <div class="proposal-doc">
                            <div class="proposal-doc-header">
                                <h3>Proposal Program Kerja KKN</h3>
                                <h2>{{ $kelompok->nama_kelompok }}</h2>
                                <div class="doc-meta">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $kelompok->desaGelombang->desa->nama_desa ?? '-' }},
                                    {{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }},
                                    {{ $kelompok->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}
                                </div>
                            </div>
                            <div class="proposal-doc-body">
                                @foreach(['pendahuluan'=>'Pendahuluan','tujuan'=>'Tujuan','manfaat'=>'Manfaat','hasil_observasi'=>'Hasil Observasi','rancangan_program'=>'Rancangan Program','solusi_ide'=>'Solusi & Ide'] as $f=>$l)
                                <h4>{{ $l }}</h4>
                                @php $content = $proposal->$f; $isEmpty = !$content || trim(strip_tags($content)) === ''; @endphp
                                @if($isEmpty)<p class="text-muted text-center">Belum ada {{ $l }}</p>@else<p>{!! $content !!}</p>@endif
                                @endforeach
                            </div>
                        </div>
                            @if($proposal->status==='diajukan')
                            <div class="card mt-3">
                                <div class="card-header"><h4>Review (DPL)</h4></div>
                                <div class="card-body">
                                    <form action="{{ route('kelompok.proposal.review', $proposal->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group"><label>Komentar</label><textarea name="komentar_dpl" class="form-control" rows="3"></textarea></div>
                                        <button name="action" value="setujui" class="btn btn-success" onclick="return confirm('Setujui?')"><i class="fas fa-check mr-1"></i> Setujui</button>
                                        <button name="action" value="tolak" class="btn btn-danger" onclick="return confirm('Tolak?')"><i class="fas fa-times mr-1"></i> Tolak</button>
                                    </form>
                                </div>
                            </div>
                            @endif
                        @else <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">📄</span><h5>Belum Ada Proposal</h5><p class="text-muted">Ketua kelompok belum membuat proposal.</p></div></div> @endif
                    </div>
                    <div class="tab-content" id="tab-tugas">
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
                        <div class="card mb-2 border-danger"><div class="card-header bg-danger text-white py-1"><strong><i class="fas fa-star mr-1"></i>Tugas Wajib</strong></div><div class="card-body p-0">
                            @foreach($wajibTasks as $kat => $items)
                            @if($items->count())
                            <div class="px-3 py-1 task-cat-header border-bottom"><small class="font-weight-bold">{{ $katLabels[$kat] ?? $kat }}</small></div>
                            @foreach($items as $t)
                            <div class="px-3 py-2 border-bottom"><strong class="small">{{ $t->nama_tugas }}</strong> <span class="badge badge-danger" style="font-size:9px;">Wajib</span>
                                @if($t->submissions->count())
                                <table class="table table-sm mb-0"><tr><th>Judul</th><th>Oleh</th><th>Status</th><th>Aksi</th></tr>
                                @foreach($t->submissions as $s)
                                <tr><td>{{ $s->judul }}</td><td>{{ $s->pesertaKkn->mahasiswa->user->name ?? '-' }}</td><td><span class="badge badge-{{ $s->status==='diterima'?'success':'info' }}">{{ $s->status }}</span></td>
                                    <td><form action="{{ route('kelompok.tugas.review', $s->id) }}" method="POST" class="form-inline gap-1">@csrf
                                        <input name="komentar_dpl" class="form-control form-control-sm" placeholder="Komentar" style="width:80px;">
                                        <button name="status" value="diterima" class="btn btn-sm btn-success">✓</button>
                                        <button name="status" value="ditolak" class="btn btn-sm btn-danger">✗</button>
                                    </form></td></tr>
                                @endforeach</table>
                                @else <p class="text-muted small px-3 pb-2">Belum ada pengumpulan</p> @endif
                            </div>
                            @endforeach
                            @endif
                            @endforeach
                        </div></div>
                        @endif

                        @if($otherTasks->sum(fn($g) => $g->count()) > 0)
                        <div class="card"><div class="card-header py-1"><strong><i class="fas fa-list mr-1"></i>Tugas Lainnya</strong></div><div class="card-body p-0">
                            @foreach($otherTasks as $kat => $items)
                            @if($items->count())
                            <div class="px-3 py-1 task-cat-header border-bottom"><small class="font-weight-bold">{{ $katLabels[$kat] ?? $kat }}</small></div>
                            @foreach($items as $t)
                            <div class="px-3 py-2 border-bottom"><strong class="small">{{ $t->nama_tugas }}</strong>
                                @if($t->submissions->count())
                                <table class="table table-sm mb-0"><tr><th>Judul</th><th>Oleh</th><th>Status</th><th>Aksi</th></tr>
                                @foreach($t->submissions as $s)
                                <tr><td>{{ $s->judul }}</td><td>{{ $s->pesertaKkn->mahasiswa->user->name ?? '-' }}</td><td><span class="badge badge-{{ $s->status==='diterima'?'success':'info' }}">{{ $s->status }}</span></td>
                                    <td><form action="{{ route('kelompok.tugas.review', $s->id) }}" method="POST" class="form-inline gap-1">@csrf
                                        <input name="komentar_dpl" class="form-control form-control-sm" placeholder="Komentar" style="width:80px;">
                                        <button name="status" value="diterima" class="btn btn-sm btn-success">✓</button>
                                        <button name="status" value="ditolak" class="btn btn-sm btn-danger">✗</button>
                                    </form></td></tr>
                                @endforeach</table>
                                @else <p class="text-muted small px-3 pb-2">Belum ada pengumpulan</p> @endif
                            </div>
                            @endforeach
                            @endif
                            @endforeach
                        </div></div>
                        @endif

                        @if($wajibTasks->sum(fn($g) => $g->count()) == 0 && $otherTasks->sum(fn($g) => $g->count()) == 0)
                        <div class="card"><div class="card-body text-muted text-center py-4">Belum ada tugas.</div></div>
                        @endif
                        @else <div class="card"><div class="card-body text-muted text-center py-4">Belum ada tugas.</div></div> @endif
                    </div>
                    <div class="tab-content" id="tab-logbook">
                        @if($logbookData->count())
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
                                                <input type="text" class="form-control dpl-logbook-search" placeholder="Cari judul, deskripsi, atau tanggal...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Tampilkan</label>
                                                <select class="form-control form-control-sm dpl-logbook-perpage" style="width:80px;">
                                                    <option value="10">10</option>
                                                    <option value="25" selected>25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Urutkan</label>
                                                <select class="form-control form-control-sm dpl-logbook-sortby" style="width:110px;">
                                                    <option value="tanggal">Tanggal</option>
                                                    <option value="judul">Judul</option>
                                                    <option value="deskripsi">Deskripsi</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small font-weight-bold">Tipe</label>
                                                <select class="form-control form-control-sm dpl-logbook-order" style="width:90px;">
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
                                            <input type="checkbox" class="custom-switch-input logbook-toggle dpl-logbook-toggle-doc" id="dplLogbookToggleDoc" checked>
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description ml-2" style="font-size:13px;">Tampilkan Dokumen Langsung</span>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        @foreach($logbookData as $pesertaId => $entries)
                        @php $member = $entries->first()->pesertaKkn->mahasiswa->user; $v = $entries->where('is_validated',true)->count(); @endphp
                        <div class="card border-0 shadow-sm mb-3 logbook-member-card">
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
                                                <td class="text-justify" style="max-width:300px;"><small>{{ \Illuminate\Support\Str::limit($lb->deskripsi, 200) }}</small></td>
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
                        @else <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5"><span style="font-size:48px;">📖</span><h5>Belum Ada Log Book</h5></div></div> @endif
                    </div>
                    <div class="tab-content" id="tab-penilaian">
                        @if($komponenList->count())
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-bottom">
                                <h4 class="mb-0">Penilaian Per Anggota</h4>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle mb-0" style="border-collapse:collapse;">
                                        <thead>
                                            <tr style="background:#2D3A8A;">
                                                <th class="text-white py-3" width="200"><i class="fas fa-user mr-2"></i>Anggota</th>
                                                @foreach($komponenList->where('kategori','dpl') as $k)
                                                <th class="text-white text-center py-3" width="180">{{ $k->nama_komponen }}<br><small class="text-white-50">{{ $k->bobot }}%</small></th>
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
                                                @foreach($komponenList->where('kategori','dpl') as $k)
                                                @php $nilai = $penilaianIndividu[$p->id][$k->id]->nilai ?? null; @endphp
                                                <td class="text-center py-2">
                                                    <form action="{{ route('kelompok.penilaian.input') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok->id }}">
                                                        <input type="hidden" name="komponen_id" value="{{ $k->id }}">
                                                        <input type="hidden" name="peserta_kkn_id" value="{{ $p->id }}">
                                                        <div class="d-flex justify-content-center">
                                                            <input type="number" name="nilai" class="form-control form-control-sm text-center rounded-right-0" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width:75px;">
                                                            <button class="btn btn-sm btn-primary rounded-left-0"><i class="fas fa-save"></i></button>
                                                        </div>
                                                    </form>
                                                </td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                            @if($kelompok->pesertaKkn->count() === 0)
                                            <tr><td colspan="{{ $komponenList->where('kategori','dpl')->count() + 1 }}" class="text-center text-muted py-4">Belum ada anggota.</td></tr>
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
                </div>
            </div>
        </div>
    </div>
</section>
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
    document.getElementById('anggotaSearch')?.addEventListener('keyup', function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#anggotaTable tbody tr').forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
    (function() {
        function applyDplLogbookFilters() {
            var search = (document.querySelector('.dpl-logbook-search')?.value || '').toLowerCase();
            var perPage = parseInt(document.querySelector('.dpl-logbook-perpage')?.value || 25);
            var sortBy = document.querySelector('.dpl-logbook-sortby')?.value || 'tanggal';
            var order = document.querySelector('.dpl-logbook-order')?.value || 'desc';
            var showDoc = document.querySelector('.dpl-logbook-toggle-doc')?.checked || false;
            document.querySelectorAll('.logbook-member-card').forEach(function(card) {
                var tbody = card.querySelector('tbody');
                if (!tbody) return;
                var rows = Array.from(tbody.querySelectorAll('.logbook-row'));
                var info = card.querySelector('.logbook-info');
                var showingEl = card.querySelector('.lb-showing');
                var totalEl = card.querySelector('.lb-total');
                var filtered = rows.filter(function(r) {
                    if (!search) return true;
                    return (r.dataset.judul + ' ' + r.dataset.deskripsi + ' ' + r.dataset.tanggal).includes(search);
                });
                filtered.sort(function(a, b) {
                    var valA = a.dataset[sortBy] || '', valB = b.dataset[sortBy] || '';
                    if (order === 'desc') return valA < valB ? 1 : -1;
                    return valA > valB ? 1 : -1;
                });
                var total = filtered.length;
                var shown = filtered.slice(0, perPage);
                rows.forEach(function(r) {
                    var d = r.querySelector('.logbook-download');
                    var p = r.querySelector('.logbook-preview');
                    if (d) d.style.display = showDoc ? 'none' : '';
                    if (p) p.style.display = showDoc ? '' : 'none';
                });
                rows.forEach(function(r) { r.style.display = 'none'; });
                shown.forEach(function(r) { r.style.display = ''; });
                if (showingEl) showingEl.textContent = Math.min(perPage, total);
                if (totalEl) totalEl.textContent = total;
                if (info) info.style.display = total === 0 ? 'none' : '';
            });
        }
        document.querySelector('.dpl-logbook-search')?.addEventListener('keyup', applyDplLogbookFilters);
        document.querySelector('.dpl-logbook-perpage')?.addEventListener('change', applyDplLogbookFilters);
        document.querySelector('.dpl-logbook-sortby')?.addEventListener('change', applyDplLogbookFilters);
        document.querySelector('.dpl-logbook-order')?.addEventListener('change', applyDplLogbookFilters);
        document.querySelector('.dpl-logbook-toggle-doc')?.addEventListener('change', applyDplLogbookFilters);
        setTimeout(applyDplLogbookFilters, 100);
    })();
</script>
@endpush
