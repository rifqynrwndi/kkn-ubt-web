@extends('layouts.app')

@section('title', 'Monitor WAR — ' . $war->name)

@push('css')
<style>
.monitor-header {
    background: linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);
    border-radius:16px; padding:28px 32px; color:#fff; margin-bottom:24px;
    position:relative; overflow:hidden;
}
.monitor-header::after {
    content:''; position:absolute; top:-40px; right:-40px;
    width:160px; height:160px;
    background:rgba(103,119,239,.12); border-radius:50%; pointer-events:none;
}
.monitor-header h1 { font-size:1.7rem; font-weight:800; margin:0 0 4px; }
.monitor-header p  { font-size:.875rem; opacity:.7; margin:0; }

.live-dot {
    display:inline-block;
    width:9px; height:9px;
    background:#47c363;
    border-radius:50%;
    animation:blink-dot 1.2s infinite;
    margin-right:6px;
}
@keyframes blink-dot { 0%,100%{opacity:1}50%{opacity:0} }

/* Stats Cards */
.mstat { border:none; border-radius:14px; padding:20px 22px; overflow:hidden; }
.mstat .ms-icon { font-size:28px; margin-bottom:10px; }
.mstat .ms-val  { font-size:2rem; font-weight:800; line-height:1; margin-bottom:4px; }
.mstat .ms-lbl  { font-size:12px; opacity:.75; }
.ms-blue   { background:linear-gradient(135deg,#6777ef,#4f5ece); color:#fff; }
.ms-green  { background:linear-gradient(135deg,#47c363,#2f9e44); color:#fff; }
.ms-red    { background:linear-gradient(135deg,#fc544b,#c92a2a); color:#fff; }
.ms-orange { background:linear-gradient(135deg,#ffa426,#d08800); color:#fff; }

/* Fakultas progress */
.fak-row { margin-bottom:12px; }
.fak-row .fak-name { font-size:13px; font-weight:600; margin-bottom:4px; }
.fak-row .fak-bar  { height:8px; border-radius:6px; background:#e9ecef; overflow:hidden; }
.fak-row .fak-fill { height:100%; border-radius:6px; transition:width .5s; background:linear-gradient(90deg,#6777ef,#4f5ece); }
.fak-row .fak-meta { display:flex; justify-content:space-between; font-size:11px; color:#adb5bd; margin-top:3px; }

/* Kelompok Grid */
.k-card {
    border:2px solid #e9ecef; border-radius:12px; padding:14px;
    height:100%; transition:border-color .2s;
}
.k-card.is-full { border-color:#ffd6d6; background:#fff8f8; }
.k-card .k-nama { font-weight:700; font-size:14px; margin-bottom:2px; }
.k-card .k-desa { font-size:11px; color:#adb5bd; margin-bottom:8px; }
.k-card .k-bar  { height:5px; border-radius:4px; background:#e9ecef; overflow:hidden; margin:6px 0; }
.k-card .k-fill { height:100%; border-radius:4px; transition:width .4s; }
.k-fill.f-ok    { background:linear-gradient(90deg,#47c363,#2f9e44); }
.k-fill.f-warn  { background:linear-gradient(90deg,#ffa426,#e67700); }
.k-fill.f-full  { background:linear-gradient(90deg,#fc544b,#c92a2a); }
.k-card .k-count{ font-size:11px; color:#6c757d; }

/* Log Feed */
.log-feed { max-height:360px; overflow-y:auto; }
.log-item {
    padding:8px 12px; border-left:3px solid #e9ecef;
    margin-bottom:6px; border-radius:0 8px 8px 0;
    font-size:13px; background:#fafafa;
    transition:background .3s;
}
.log-item.join_success  { border-left-color:#47c363; }
.log-item.join_failed   { border-left-color:#fc544b; }
.log-item .log-time     { font-size:11px; color:#adb5bd; margin-top:2px; }
.log-item.new           { background:#f0fff4; animation:fadeLog .8s; }
@keyframes fadeLog { from{background:#d3f9df} to{background:#f0fff4} }

/* Countdown */
#mon-countdown { font-family:'Courier New',monospace; font-weight:800; font-size:1.4rem; color:#ff6b6b; }

/* Pulse on refresh */
.refreshing { opacity:.6; transition:opacity .2s; }
</style>
@endpush

@section('content')

<section class="section">

    <div class="section-header">
        <h1><span class="live-dot"></span> Monitor WAR Live</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.war.index') }}">WAR Admin</a></div>
            <div class="breadcrumb-item">Monitor</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ── HEADER ──────────────────────────────── --}}
        <div class="monitor-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div style="font-size:12px;opacity:.6;margin-bottom:8px;">
                        <span class="live-dot"></span> LIVE MONITORING
                    </div>
                    <h1>{{ $war->name }}</h1>
                    <p>
                        Gelombang: <strong>{{ $war->gelombang->nama_gelombang ?? '-' }}</strong>
                        &nbsp;·&nbsp;
                        Berakhir: <strong>{{ $war->end_at?->format('d M Y, H:i') }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <div style="font-size:12px;opacity:.6;margin-bottom:4px;">Waktu Tersisa</div>
                    <div id="mon-countdown">–</div>
                    <div class="mt-3 d-flex gap-2 justify-content-md-end">
                        <a href="{{ route('admin.war.monitor.exportLog', $war) }}"
                           class="btn btn-sm btn-outline-light">
                            <i class="fas fa-download mr-1"></i> Export Log
                        </a>
                        <form action="{{ route('admin.war.stop', $war) }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-danger"
                                    onclick="return confirm('Hentikan WAR sekarang?')">
                                <i class="fas fa-stop mr-1"></i> Stop WAR
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STATS CARDS ─────────────────────────── --}}
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="mstat ms-blue">
                    <div class="ms-icon"><i class="fas fa-users"></i></div>
                    <div class="ms-val" id="ms-peserta">{{ $war->participants_count }}</div>
                    <div class="ms-lbl">Total Peserta Bergabung</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="mstat ms-green">
                    <div class="ms-icon"><i class="fas fa-home"></i></div>
                    <div class="ms-val" id="ms-sisa">–</div>
                    <div class="ms-lbl">Kelompok Tersisa</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="mstat ms-red">
                    <div class="ms-icon"><i class="fas fa-lock"></i></div>
                    <div class="ms-val" id="ms-penuh">–</div>
                    <div class="ms-lbl">Kelompok Penuh</div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="mstat ms-orange">
                    <div class="ms-icon"><i class="fas fa-chart-bar"></i></div>
                    <div class="ms-val" id="ms-total">{{ $kelompoks->count() }}</div>
                    <div class="ms-lbl">Total Kelompok</div>
                </div>
            </div>
        </div>

        <div class="row">

            {{-- ── KUOTA FAKULTAS ───────────────────── --}}
            <div class="col-md-4 mb-4">
                <div class="card" style="border-radius:14px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
                    <div class="card-header bg-transparent border-bottom" style="padding:16px 20px;font-weight:700;">
                        <i class="fas fa-clipboard-list text-primary mr-1"></i> Kuota per Fakultas
                    </div>
                    <div class="card-body" id="fak-container" style="padding:20px;">
                        @foreach($war->faculties as $wf)
                        <div class="fak-row" id="fak-{{ $wf->fakultas_id }}">
                            <div class="fak-name">{{ $wf->fakultas?->nama_fakultas ?? 'N/A' }}</div>
                            <div class="fak-bar">
                                <div class="fak-fill"
                                     id="fak-bar-{{ $wf->fakultas_id }}"
                                     style="width:{{ $wf->quota > 0 ? round(($wf->filled/$wf->quota)*100) : 0 }}%">
                                </div>
                            </div>
                            <div class="fak-meta">
                                <span id="fak-taken-{{ $wf->fakultas_id }}">{{ $wf->filled }}/{{ $wf->quota }}</span>
                                <span id="fak-pct-{{ $wf->fakultas_id }}">
                                    {{ $wf->quota > 0 ? round(($wf->filled/$wf->quota)*100) : 0 }}%
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── ACTIVITY LOG ─────────────────────── --}}
            <div class="col-md-4 mb-4">
                <div class="card" style="border-radius:14px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
                    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between" style="padding:16px 20px;font-weight:700;">
                        <span><i class="fas fa-bolt text-warning mr-1"></i> Aktivitas Terbaru</span>
                        <small class="text-muted font-weight-normal" id="log-updated"></small>
                    </div>
                    <div class="card-body p-0">
                        <div class="log-feed p-3" id="log-feed">
                            <p class="text-muted text-center py-4" style="font-size:13px;">Memuat log...</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── KELOMPOK GRID ────────────────────── --}}
            <div class="col-md-4 mb-4">
                <div class="card" style="border-radius:14px;border:none;box-shadow:0 4px 20px rgba(0,0,0,.06);">
                    <div class="card-header bg-transparent border-bottom" style="padding:16px 20px;font-weight:700;">
                        🏘️ Status Kelompok
                    </div>
                    <div class="card-body p-2" style="max-height:460px;overflow-y:auto;">
                        <div class="row" id="kelompok-mini-grid">
                            @foreach($kelompoks as $k)
                            @php
                                $t = $k->pesertaKkn->count();
                                $q = $k->kuota;
                                $p = $q > 0 ? round(($t/$q)*100) : 0;
                                $fc = $p >= 100 ? 'f-full' : ($p >= 70 ? 'f-warn' : 'f-ok');
                            @endphp
                            <div class="col-6 mb-2">
                                <div class="k-card {{ $k->is_full ? 'is-full' : '' }}"
                                     id="kcard-{{ $k->id }}">
                                    <div class="k-nama">{{ Str::limit($k->nama_kelompok, 18) }}</div>
                                    <div class="k-desa">📍 {{ Str::limit($k->desaGelombang?->desa?->nama_desa ?? '-', 16) }}</div>
                                    <div class="k-bar">
                                        <div class="k-fill {{ $fc }}"
                                             id="kfill-{{ $k->id }}"
                                             style="width:{{ $p }}%">
                                        </div>
                                    </div>
                                    <div class="k-count" id="kcount-{{ $k->id }}">
                                        {{ $t }}/{{ $q }} anggota
                                    </div>
                                </div>
                            </div>
                            @endforeach
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
    const END_AT     = new Date("{{ $war->end_at?->toISOString() }}");

    let lastLogId = 0;

    /* ── COUNTDOWN ──────────────────────────────────── */
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

    /* ── FETCH STATS ────────────────────────────────── */
    function fetchStats() {
        fetch(STATS_URL, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(d => {
                document.getElementById('ms-peserta').textContent = d.total_peserta ?? '–';
                document.getElementById('ms-sisa').textContent    = d.kelompok?.tersisa ?? '–';
                document.getElementById('ms-penuh').textContent   = d.kelompok?.penuh   ?? '–';

                // Update fakultas bars
                (d.fakultas || []).forEach(f => {
                    const bar    = document.getElementById(`fak-bar-${f.fakultas_id ?? ''}`);
                    const taken  = document.getElementById(`fak-taken-${f.fakultas_id ?? ''}`);
                    const pct    = document.getElementById(`fak-pct-${f.fakultas_id ?? ''}`);
                    if (bar)   bar.style.width     = f.persen + '%';
                    if (taken) taken.textContent    = `${f.filled}/${f.quota}`;
                    if (pct)   pct.textContent      = f.persen + '%';
                });
            })
            .catch(() => {});
    }

    /* ── FETCH LOGS ─────────────────────────────────── */
    function fetchLogs() {
        fetch(LOGS_URL + '?limit=20', { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(d => {
                const feed = document.getElementById('log-feed');
                if (!d.logs.length) {
                    feed.innerHTML = '<p class="text-muted text-center py-4" style="font-size:13px;">Belum ada aktivitas</p>';
                    return;
                }

                // Prepend new logs
                const newLogs = d.logs.filter(l => l.id > lastLogId);
                if (newLogs.length) {
                    lastLogId = Math.max(...d.logs.map(l => l.id));
                }

                feed.innerHTML = d.logs.map(l => `
                    <div class="log-item ${l.action} ${l.id > (lastLogId - newLogs.length) && newLogs.length ? 'new' : ''}">
                        <strong>${l.peserta}</strong>
                        ${l.action === 'join_success'
                            ? `→ bergabung ke <strong>${l.meta?.kelompok_nama ?? '-'}</strong>`
                            : `<span class="text-danger">${l.action}</span>`
                        }
                        <div class="log-time">${l.human}</div>
                    </div>
                `).join('');

                document.getElementById('log-updated').textContent = 'diperbarui ' + new Date().toLocaleTimeString('id-ID');
            })
            .catch(() => {});
    }

    /* ── FETCH KELOMPOKS ─────────────────────────────── */
    function fetchKelompoks() {
        fetch(KK_URL, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(d => {
                d.kelompoks.forEach(k => {
                    const fill  = document.getElementById(`kfill-${k.id}`);
                    const count = document.getElementById(`kcount-${k.id}`);
                    const card  = document.getElementById(`kcard-${k.id}`);
                    if (!fill || !count) return;

                    const pct = k.kuota > 0 ? Math.round((k.terisi / k.kuota) * 100) : 0;
                    fill.style.width = pct + '%';
                    fill.className   = 'k-fill ' + (pct >= 100 ? 'f-full' : pct >= 70 ? 'f-warn' : 'f-ok');
                    count.textContent = `${k.terisi}/${k.kuota} anggota`;

                    if (k.is_full) card?.classList.add('is-full');
                });
            })
            .catch(() => {});
    }

    // Initial + intervals
    fetchStats();    setInterval(fetchStats,    8000);
    fetchLogs();     setInterval(fetchLogs,     5000);
    fetchKelompoks();setInterval(fetchKelompoks,7000);

})();
</script>
@endpush
