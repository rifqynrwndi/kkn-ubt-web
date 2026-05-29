@extends('layouts.app')

@section('title', 'Monitor WAR — ' . $war->name)

@push('css')
<style>
/* ── HEADER ──────────────────────────────── */
.mon-header {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    border-radius: 20px;
    padding: 28px 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(15, 52, 96, .35);
}
.mon-header::before {
    content: '';
    position: absolute;
    top: -70px; right: -70px;
    width: 260px; height: 260px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.mon-header::after {
    content: '';
    position: absolute;
    bottom: -50px; left: 20%;
    width: 180px; height: 180px;
    background: rgba(103,119,239,.08);
    border-radius: 50%;
}
.mh-content { position: relative; z-index: 1; }
.mh-live {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    margin-bottom: 10px;
    background: rgba(255,255,255,.12);
    padding: 4px 14px;
    border-radius: 50px;
}
.mh-live-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #47c363;
    animation: pulse-dot 1.3s infinite;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(71,195,99,.6); }
    50%      { opacity: .4; box-shadow: 0 0 0 8px rgba(71,195,99,0); }
}
.mh-title {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -.5px;
}
.mh-meta {
    font-size: .85rem;
    opacity: .75;
    margin-top: 4px;
}
.mh-countdown {
    font-family: 'Courier New', monospace;
    font-size: 1.7rem;
    font-weight: 700;
    letter-spacing: 2px;
}
.mh-status {
    display: inline-block;
    padding: 5px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .5px;
    text-transform: uppercase;
}
.mh-status.active   { background: #47c363; color: #fff; }
.mh-status.scheduled{ background: rgba(255,255,255,.2); }
.mh-status.closed   { background: #fc544b; color: #fff; }
.mh-status.stopped  { background: #6c757d; color: #fff; }

/* ── STAT CARDS ──────────────────────────── */
.stat-card {
    border-radius: 18px;
    padding: 22px 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
    height: 100%;
}
.stat-card::after {
    content: '';
    position: absolute;
    right: -24px; bottom: -24px;
    width: 100px; height: 100px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
}
.sc-icon  { font-size: 22px; opacity: .85; margin-bottom: 12px; }
.sc-value { font-size: 1.8rem; font-weight: 800; line-height: 1; }
.sc-label { margin-top: 5px; font-size: 12px; opacity: .75; }
.sc-blue   { background: linear-gradient(135deg, #6777ef, #4f5ece); }
.sc-green  { background: linear-gradient(135deg, #47c363, #2f9e44); }
.sc-purple { background: linear-gradient(135deg, #8860d0, #6c3bb3); }
.sc-orange { background: linear-gradient(135deg, #ffa426, #e67700); }

/* ── SECTION CARDS ───────────────────────── */
.sec-card {
    border: none;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 2px 16px rgba(0,0,0,.05);
    background: var(--bs-body-bg, #fff);
    margin-bottom: 24px;
}
.sec-card .card-header {
    background: transparent;
    border-bottom: 1px solid rgba(0,0,0,.06);
    padding: 16px 22px;
    font-weight: 700;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ── KELOMPOK TABLE ──────────────────────── */
.kl-table { width: 100%; }
.kl-row {
    display: flex;
    align-items: center;
    padding: 13px 22px;
    border-bottom: 1px solid rgba(0,0,0,.05);
    gap: 16px;
    transition: background .15s;
}
.kl-row:last-child { border-bottom: none; }
.kl-row:hover { background: rgba(103,119,239,.03); }
.kl-row.is-full { opacity: .7; }
.kl-num {
    width: 32px; height: 32px;
    border-radius: 10px;
    background: #eef0ff;
    color: #6777ef;
    font-weight: 800;
    font-size: 13px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.kl-info { flex: 1; min-width: 0; }
.kl-name {
    font-size: 14px;
    font-weight: 700;
    color: #1a1a2e;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kl-loc {
    font-size: 12px;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 1px;
}
.kl-bar-col { width: 130px; flex-shrink: 0; }
.kl-bar {
    height: 7px;
    border-radius: 999px;
    overflow: hidden;
    background: #e9ecef;
}
.kl-bar-fill { height: 100%; border-radius: 999px; transition: width .4s; }
.kl-bar-fill.ok   { background: linear-gradient(90deg, #47c363, #2f9e44); }
.kl-bar-fill.warn { background: linear-gradient(90deg, #ffa426, #e67700); }
.kl-bar-fill.full { background: linear-gradient(90deg, #fc544b, #c92a2a); }
.kl-count {
    font-size: 11px;
    color: #6c757d;
    text-align: center;
    margin-top: 3px;
    white-space: nowrap;
}
.kl-badge {
    flex-shrink: 0;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .3px;
    min-width: 70px;
    text-align: center;
}
.kl-badge.available { background: #e8f5e9; color: #2f9e44; }
.kl-badge.full      { background: #ffebee; color: #c92a2a; }
.kl-badge.near      { background: #fff8e1; color: #e67700; }

/* ── FAKULTAS ROW ────────────────────────── */
.fak-row { margin-bottom: 16px; }
.fak-row:last-child { margin-bottom: 0; }
.fak-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}
.fak-name { font-size: 13px; font-weight: 700; }
.fak-count { font-size: 12px; color: var(--bs-secondary-color, #6c757d); }
.fak-bar {
    height: 8px;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(0,0,0,.06);
    margin-bottom: 4px;
}
.fak-fill {
    height: 100%;
    border-radius: 999px;
    transition: width .4s;
    background: linear-gradient(90deg, #6777ef, #4f5ece);
}
.fak-pct { font-size: 11px; color: var(--bs-secondary-color, #6c757d); text-align: right; }
.fak-empty { padding: 24px; text-align: center; font-size: 13px; color: #adb5bd; }

/* ── LOG ─────────────────────────────────── */
.log-feed { max-height: 400px; overflow-y: auto; }
.log-item {
    padding: 10px 14px;
    margin: 0 3px 8px;
    border-radius: 10px;
    background: rgba(0,0,0,.02);
    border-left: 3px solid transparent;
    transition: background .15s;
}
.log-item.join_success { border-left-color: #47c363; }
.log-item.join_failed  { border-left-color: #fc544b; }
.log-name  { font-size: 13px; font-weight: 600; }
.log-dest  { font-size: 12px; margin-top: 1px; }
.log-time  { font-size: 11px; color: #adb5bd; margin-top: 3px; }
.log-empty { padding: 32px; text-align: center; font-size: 13px; color: #adb5bd; }

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
                    <div class="col-md-7">
                        <div class="mh-live">
                            <span class="mh-live-dot"></span> Live Monitoring
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
                    <div class="col-md-5 text-md-right mt-3 mt-md-0">
                        <div style="font-size:11px;opacity:.55;margin-bottom:2px;text-transform:uppercase;letter-spacing:.5px;">Waktu Tersisa</div>
                        <div class="mh-countdown" id="mon-countdown">--:--:--</div>
                        <div class="mt-3 d-flex gap-2 justify-content-md-end">
                            <a href="{{ route('admin.war.monitor.exportLog', $war) }}" class="btn btn-sm btn-outline-light">
                                <i class="fas fa-download mr-1"></i> Export Log
                            </a>
                            @if($war->status === 'active')
                            <form action="{{ route('admin.war.stop', $war) }}" method="POST" class="m-0">
                                @csrf
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Hentikan WAR sekarang?')">
                                    <i class="fas fa-stop mr-1"></i> Stop WAR
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STATS ────────────────────────────────── --}}
        <div class="row mb-4">
            <div class="col-6 col-xl-3 mb-3">
                <div class="stat-card sc-blue">
                    <div class="sc-icon"><i class="fas fa-user-check"></i></div>
                    <div class="sc-value" id="ms-joined">{{ $war->participants_count }}</div>
                    <div class="sc-label">Peserta Bergabung</div>
                </div>
            </div>
            <div class="col-6 col-xl-3 mb-3">
                <div class="stat-card sc-purple">
                    <div class="sc-icon"><i class="fas fa-users"></i></div>
                    <div class="sc-value" id="ms-total-peserta">{{ $totalPesertaGelombang }}</div>
                    <div class="sc-label">Total Peserta Gelombang</div>
                </div>
            </div>
            <div class="col-6 col-xl-3 mb-3">
                <div class="stat-card sc-green">
                    <div class="sc-icon"><i class="fas fa-home"></i></div>
                    <div class="sc-value" id="ms-available">{{ $kelompokTersedia }}</div>
                    <div class="sc-label">Kelompok Tersedia</div>
                </div>
            </div>
            <div class="col-6 col-xl-3 mb-3">
                <div class="stat-card sc-orange">
                    <div class="sc-icon"><i class="fas fa-lock"></i></div>
                    <div class="sc-value" id="ms-full">{{ $kelompokPenuh }}</div>
                    <div class="sc-label">Kelompok Penuh</div>
                </div>
            </div>
        </div>

        {{-- ── MAIN CONTENT ────────────────────────── --}}
        <div class="row">

            {{-- DAFTAR KELOMPOK ─────────────────────── --}}
            <div class="col-lg-7 mb-4">
                <div class="sec-card">
                    <div class="card-header">
                        <span><i class="fas fa-layer-group text-primary mr-2"></i> Daftar Kelompok</span>
                        <span class="badge badge-light" style="font-size:12px;border-radius:20px;padding:5px 14px;" id="kl-total-badge">{{ $kelompoks->count() }} kelompok</span>
                    </div>
                    <div class="card-body p-0" id="kelompok-list">
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
                                <div class="kl-num">{{ $index + 1 }}</div>
                                <div class="kl-info">
                                    <div class="kl-name">{{ $k->nama_kelompok }}</div>
                                    <div class="kl-loc">
                                        <i class="fas fa-map-marker-alt text-danger mr-1" style="font-size:10px;"></i>
                                        {{ $k->desaGelombang->desa->nama_desa ?? '-' }},
                                        {{ $k->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}
                                        @if(isset($k->desaGelombang->desa->kecamatan->kabupaten))
                                            , {{ $k->desaGelombang->desa->kecamatan->kabupaten }}
                                        @endif
                                    </div>
                                </div>
                                <div class="kl-bar-col">
                                    <div class="kl-bar">
                                        <div class="kl-bar-fill {{ $barClass }}" id="kfill-{{ $k->id }}" style="width:{{ $p }}%"></div>
                                    </div>
                                    <div class="kl-count" id="kcount-{{ $k->id }}">{{ $t }}/{{ $q }}</div>
                                </div>
                                <div class="kl-badge {{ $badgeClass }}" id="kbadge-{{ $k->id }}">{{ $badgeText }}</div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Tidak ada kelompok tersedia.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- SIDEBAR: FAKULTAS + LOG ─────────────── --}}
            <div class="col-lg-5 mb-4">

                {{-- KUOTA PER FAKULTAS ──────────────── --}}
                <div class="sec-card">
                    <div class="card-header">
                        <span><i class="fas fa-building text-primary mr-2"></i> Progress per Fakultas</span>
                        <small class="text-muted font-weight-normal" id="fak-updated"></small>
                    </div>
                    <div class="card-body" id="fak-container">
                        @forelse($fakultasStats as $fs)
                        <div class="fak-row" id="fak-{{ $fs['fakultas_id'] }}">
                            <div class="fak-head">
                                <span class="fak-name">{{ $fs['nama'] }}</span>
                                <span class="fak-count" id="fak-taken-{{ $fs['fakultas_id'] }}">{{ $fs['filled'] }}/{{ $fs['total'] }}</span>
                            </div>
                            <div class="fak-bar">
                                <div class="fak-fill" id="fak-bar-{{ $fs['fakultas_id'] }}" style="width:{{ $fs['persen'] }}%"></div>
                            </div>
                            <div class="fak-pct" id="fak-pct-{{ $fs['fakultas_id'] }}">{{ $fs['persen'] }}%</div>
                        </div>
                        @empty
                        <div class="fak-empty">
                            <i class="fas fa-info-circle mb-1 d-block"></i>
                            Belum ada data peserta di gelombang ini.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- AKTIVITAS TERBARU ────────────────── --}}
                <div class="sec-card">
                    <div class="card-header">
                        <span><i class="fas fa-bolt text-warning mr-2"></i> Aktivitas Terbaru</span>
                        <small class="text-muted font-weight-normal" id="log-updated"></small>
                    </div>
                    <div class="card-body p-0">
                        <div class="log-feed p-3" id="log-feed">
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
                            <div class="fak-bar">
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
