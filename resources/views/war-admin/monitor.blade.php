@extends('layouts.app')

@section('title', 'Monitor WAR — ' . $war->name)

@push('css')
<link rel="stylesheet" href="{{ asset('css/war.css') }}">
<style>
    /* ── MONITOR OVERRIDES ──────────────────────── */
    .mon-stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .mon-stat-card {
        background: var(--bs-body-bg, #fff);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.04);
        transition: transform .2s, box-shadow .2s;
    }
    .mon-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,.08);
    }
    .mon-stat-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .mon-stat-icon.blue   { background: rgba(103,119,239,.12); color: var(--war-primary); }
    .mon-stat-icon.purple { background: rgba(136,96,208,.12); color: var(--war-purple); }
    .mon-stat-icon.green  { background: rgba(71,195,99,.12); color: var(--war-success); }
    .mon-stat-icon.orange { background: rgba(255,164,38,.12); color: var(--war-warning); }
    .mon-stat-text .ms-value { font-size: 1.5rem; font-weight: 800; line-height: 1; }
    .mon-stat-text .ms-label { font-size: 12px; color: var(--war-text-muted); margin-top: 3px; }

    .kelompok-scroll {
        max-height: 480px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--war-primary) transparent;
    }
    .kelompok-scroll::-webkit-scrollbar { width: 6px; }
    .kelompok-scroll::-webkit-scrollbar-track { background: transparent; }
    .kelompok-scroll::-webkit-scrollbar-thumb { background: var(--war-primary); border-radius: 3px; }

    .sidebar-section {
        margin-bottom: 20px;
    }
    .sidebar-section:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Live WAR Monitoring</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.war.index') }}">Plotting Kelompok</a></div>
            <div class="breadcrumb-item">Monitor</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ── HEADER ──────────────────────────────── --}}
        <div class="mon-header">
            <div class="mh-content">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="mh-live">
                            <span class="mh-live-dot" aria-hidden="true"></span> Live Monitoring
                        </div>
                        <div class="mh-title">{{ $war->name }}</div>
                        <div class="mh-meta">
                            Gelombang: <strong>{{ $war->gelombang->nama_gelombang ?? '-' }}</strong>
                            &nbsp;&middot;&nbsp;
                            Berakhir: <strong>{{ \Carbon\Carbon::parse($war->end_at)->format('d M Y, H:i') }}</strong>
                            &nbsp;&middot;&nbsp;
                            <span class="mh-status {{ $war->status }}">{{ $war->status }}</span>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right mt-3 mt-md-0">
                        <div class="d-flex flex-column align-items-md-end gap-2">
                            <div>
                                <div style="font-size:11px;opacity:.55;margin-bottom:4px;text-transform:uppercase;letter-spacing:.5px;">Waktu Tersisa</div>
                                <div class="mh-countdown" id="mon-countdown" aria-live="polite" aria-label="Waktu tersisa">--:--:--</div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.war.monitor.exportLog', $war) }}" class="btn btn-sm btn-outline-light">
                                    <i class="fas fa-download mr-1" aria-hidden="true"></i> Export Log
                                </a>
                                @if($war->status === 'active')
                                <form action="{{ route('admin.war.stop', $war) }}" method="POST" class="m-0">
                                    @csrf
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Hentikan WAR sekarang?')">
                                        <i class="fas fa-stop mr-1" aria-hidden="true"></i> Stop
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STATS ────────────────────────────────── --}}
        <div class="mon-stats-row" role="region" aria-label="Statistik monitoring">
            <div class="mon-stat-card">
                <div class="mon-stat-icon blue"><i class="fas fa-user-check" aria-hidden="true"></i></div>
                <div class="mon-stat-text">
                    <div class="ms-value" id="ms-joined">{{ $war->participants_count }}</div>
                    <div class="ms-label">Peserta Bergabung</div>
                </div>
            </div>
            <div class="mon-stat-card">
                <div class="mon-stat-icon purple"><i class="fas fa-users" aria-hidden="true"></i></div>
                <div class="mon-stat-text">
                    <div class="ms-value" id="ms-total-peserta">{{ $totalPesertaGelombang }}</div>
                    <div class="ms-label">Total Peserta Gelombang</div>
                </div>
            </div>
            <div class="mon-stat-card">
                <div class="mon-stat-icon green"><i class="fas fa-home" aria-hidden="true"></i></div>
                <div class="mon-stat-text">
                    <div class="ms-value" id="ms-available">{{ $kelompokTersedia }}</div>
                    <div class="ms-label">Kelompok Tersedia</div>
                </div>
            </div>
            <div class="mon-stat-card">
                <div class="mon-stat-icon orange"><i class="fas fa-lock" aria-hidden="true"></i></div>
                <div class="mon-stat-text">
                    <div class="ms-value" id="ms-full">{{ $kelompokPenuh }}</div>
                    <div class="ms-label">Kelompok Penuh</div>
                </div>
            </div>
        </div>

        {{-- ── MAIN CONTENT ────────────────────────── --}}
        <div class="row">

            {{-- DAFTAR KELOMPOK ─────────────────────── --}}
            <div class="col-lg-7 mb-4">
                <div class="sec-card">
                    <div class="card-header">
                        <span><i class="fas fa-layer-group text-primary mr-2" aria-hidden="true"></i> Daftar Kelompok</span>
                        <span class="badge badge-light" style="font-size:12px;border-radius:20px;padding:5px 14px;" id="kl-total-badge">{{ $kelompoks->count() }} kelompok</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="kelompok-scroll" id="kelompok-list">
                            @forelse($kelompoks as $index => $k)
                                @php
                                    $t = $k->pesertaKkn->count();
                                    $q = $k->kuota;
                                    $p = $q > 0 ? round(($t/$q)*100) : 0;
                                    $barClass = $p >= 100 ? 'full' : ($p >= 70 ? 'warn' : 'ok');
                                    $badgeClass = $p >= 100 ? 'full' : ($p >= 70 ? 'near' : 'available');
                                    $badgeText = $p >= 100 ? 'Penuh' : ($p >= 70 ? 'Hampir Penuh' : 'Tersedia');
                                @endphp
                                <div class="kl-row {{ $p >= 100 ? 'is-full' : '' }}" id="krow-{{ $k->id }}">
                                    <div class="kl-num" aria-hidden="true">{{ $index + 1 }}</div>
                                    <div class="kl-info">
                                        <div class="kl-name">{{ $k->nama_kelompok }}</div>
                                        <div class="kl-loc">
                                            <i class="fas fa-map-marker-alt mr-1" style="font-size:10px;" aria-hidden="true"></i>
                                            {{ $k->desaGelombang->desa->nama_desa ?? '-' }},
                                            {{ $k->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}
                                            @if(isset($k->desaGelombang->desa->kecamatan->kabupaten))
                                                , {{ $k->desaGelombang->desa->kecamatan->kabupaten }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="kl-bar-col">
                                        <div class="kl-bar" role="progressbar" aria-valuenow="{{ $p }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kuota terisi {{ $p }}%">
                                            <div class="kl-bar-fill {{ $barClass }}" id="kfill-{{ $k->id }}" style="width:{{ $p }}%"></div>
                                        </div>
                                        <div class="kl-count" id="kcount-{{ $k->id }}">{{ $t }}/{{ $q }}</div>
                                    </div>
                                    <div class="kl-badge {{ $badgeClass }}" id="kbadge-{{ $k->id }}">{{ $badgeText }}</div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block" aria-hidden="true"></i>
                                    Tidak ada kelompok tersedia.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR: FAKULTAS + LOG ─────────────── --}}
            <div class="col-lg-5 mb-4">

                {{-- KUOTA PER FAKULTAS ──────────────── --}}
                <div class="sec-card sidebar-section">
                    <div class="card-header">
                        <span><i class="fas fa-building text-primary mr-2" aria-hidden="true"></i> Progress per Fakultas</span>
                        <small class="text-muted font-weight-normal" id="fak-updated"></small>
                    </div>
                    <div class="card-body" id="fak-container">
                        @forelse($fakultasStats as $fs)
                        <div class="fak-row" id="fak-{{ $fs['fakultas_id'] }}">
                            <div class="fak-head">
                                <span class="fak-name">{{ $fs['nama'] }}</span>
                                <span class="fak-count" id="fak-taken-{{ $fs['fakultas_id'] }}">{{ $fs['filled'] }}/{{ $fs['total'] }}</span>
                            </div>
                            <div class="fak-bar" role="progressbar" aria-valuenow="{{ $fs['persen'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progress {{ $fs['nama'] }}">
                                <div class="fak-fill" id="fak-bar-{{ $fs['fakultas_id'] }}" style="width:{{ $fs['persen'] }}%"></div>
                            </div>
                            <div class="fak-pct" id="fak-pct-{{ $fs['fakultas_id'] }}">{{ $fs['persen'] }}%</div>
                        </div>
                        @empty
                        <div class="fak-empty">
                            <i class="fas fa-info-circle mb-1 d-block" aria-hidden="true"></i>
                            Belum ada data peserta di gelombang ini.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- AKTIVITAS TERBARU ────────────────── --}}
                <div class="sec-card sidebar-section">
                    <div class="card-header">
                        <span><i class="fas fa-bolt text-warning mr-2" aria-hidden="true"></i> Aktivitas Terbaru</span>
                        <small class="text-muted font-weight-normal" id="log-updated"></small>
                    </div>
                    <div class="card-body p-0">
                        <div class="log-feed p-3" id="log-feed" role="log" aria-label="Log aktivitas terbaru">
                            <div class="log-empty" id="log-empty-state">Memuat log...</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {

    const STATS_URL  = "{{ route('admin.war.monitor.stats', $war) }}";
    const LOGS_URL   = "{{ route('admin.war.monitor.logs', $war) }}";
    const KK_URL     = "{{ route('admin.war.monitor.kelompoks', $war) }}";
    const END_AT     = new Date("{{ \Carbon\Carbon::parse($war->end_at)->toISOString() }}");

    let lastLogId = 0;

    /* ── COUNTDOWN ───────────────────────── */
    const cdEl = document.getElementById('mon-countdown');
    function tick() {
        const diff = END_AT - new Date();
        if (diff <= 0) { cdEl.textContent = 'SELESAI'; return; }
        const h = String(Math.floor(diff / 3600000)).padStart(2,'0');
        const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2,'0');
        const s = String(Math.floor((diff % 60000) / 1000)).padStart(2,'0');
        cdEl.textContent = `${h}:${m}:${s}`;
    }
    setInterval(tick, 1000); tick();

    /* ── FETCH STATS ─────────────────────── */
    function fetchStats() {
        fetch(STATS_URL, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(d => {
                document.getElementById('ms-joined').textContent        = d.total_peserta ?? '–';
                document.getElementById('ms-total-peserta').textContent = d.total_peserta_gelombang ?? '–';
                document.getElementById('ms-available').textContent     = d.kelompok?.tersisa ?? '–';
                document.getElementById('ms-full').textContent          = d.kelompok?.penuh ?? '–';

                var fakContainer = document.getElementById('fak-container');
                var fakultas = d.fakultas || [];

                if (fakultas.length === 0) {
                    fakContainer.innerHTML = '<div class="fak-empty"><i class="fas fa-info-circle mb-1 d-block"></i>Belum ada data peserta di gelombang ini.</div>';
                } else {
                    fakContainer.innerHTML = fakultas.map(function(f) {
                        return `
                        <div class="fak-row" id="fak-${f.fakultas_id}">
                            <div class="fak-head">
                                <span class="fak-name">${f.nama}</span>
                                <span class="fak-count" id="fak-taken-${f.fakultas_id}">${f.filled}/${f.quota}</span>
                            </div>
                            <div class="fak-bar" role="progressbar" aria-valuenow="${f.persen}" aria-valuemin="0" aria-valuemax="100" aria-label="Progress ${f.nama}">
                                <div class="fak-fill" id="fak-bar-${f.fakultas_id}" style="width:${f.persen}%"></div>
                            </div>
                            <div class="fak-pct" id="fak-pct-${f.fakultas_id}">${f.persen}%</div>
                        </div>`;
                    }).join('');
                }

                document.getElementById('fak-updated').textContent = 'diperbarui ' + new Date().toLocaleTimeString('id-ID');
            })
            .catch(function() {});
    }

    /* ── FETCH LOGS ──────────────────────── */
    function fetchLogs() {
        fetch(LOGS_URL + '?limit=20', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(d => {
                var feed = document.getElementById('log-feed');
                if (!d.logs.length) {
                    feed.innerHTML = '<div class="log-empty">Belum ada aktivitas.</div>';
                    return;
                }

                var newLogs = d.logs.filter(function(l) { return l.id > lastLogId; });
                if (newLogs.length && d.logs.length) {
                    lastLogId = Math.max.apply(null, d.logs.map(function(l) { return l.id; }));
                }

                feed.innerHTML = d.logs.map(function(l) {
                    var isNew = l.id > (lastLogId - newLogs.length) && newLogs.length ? 'new' : '';
                    var dest = l.meta && l.meta.kelompok_nama ? l.meta.kelompok_nama : '-';
                    var icon = l.action === 'join_success' ? '<i class="fas fa-check-circle text-success mr-1"></i>' : '<i class="fas fa-times-circle text-danger mr-1"></i>';
                    return `
                    <div class="log-item ${l.action} ${isNew ? 'new' : ''}">
                        <div class="log-name">${icon} ${l.peserta}</div>
                        <div class="log-dest">&#10132; ${dest}</div>
                        <div class="log-time">${l.human}</div>
                    </div>`;
                }).join('');

                document.getElementById('log-updated').textContent = 'diperbarui ' + new Date().toLocaleTimeString('id-ID');
            })
            .catch(function() {});
    }

    /* ── FETCH KELOMPOKS ─────────────────── */
    function fetchKelompoks() {
        fetch(KK_URL, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(d => {
                d.kelompoks.forEach(function(k) {
                    var fill  = document.getElementById('kfill-' + k.id);
                    var count = document.getElementById('kcount-' + k.id);
                    var badge = document.getElementById('kbadge-' + k.id);
                    var row   = document.getElementById('krow-' + k.id);
                    if (!fill || !count) return;

                    var pct = k.kuota > 0 ? Math.round((k.terisi / k.kuota) * 100) : 0;
                    fill.style.width = pct + '%';
                    fill.className   = 'kl-bar-fill ' + (pct >= 100 ? 'full' : pct >= 70 ? 'warn' : 'ok');
                    count.textContent = k.terisi + '/' + k.kuota;

                    if (badge) {
                        if (pct >= 100) {
                            badge.className = 'kl-badge full';
                            badge.textContent = 'Penuh';
                        } else if (pct >= 70) {
                            badge.className = 'kl-badge near';
                            badge.textContent = 'Hampir Penuh';
                        } else {
                            badge.className = 'kl-badge available';
                            badge.textContent = 'Tersedia';
                        }
                    }

                    if (row) {
                        if (pct >= 100) row.classList.add('is-full');
                        else row.classList.remove('is-full');
                    }
                });
            })
            .catch(function() {});
    }

    fetchStats();     setInterval(fetchStats,     30000);
    fetchLogs();      setInterval(fetchLogs,      30000);
    fetchKelompoks(); setInterval(fetchKelompoks, 30000);

})();
</script>
@endpush
