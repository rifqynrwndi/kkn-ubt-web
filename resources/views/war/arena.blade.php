@extends('layouts.app')

@section('title', 'Pemilihan Kelompok KKN')

@push('css')
<style>
    /* ── HEADER ARENA ─────────────────────────────── */
    .arena-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        color: white;
        padding: 32px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(15,52,96,0.4);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .arena-header::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: rgba(103,119,239,.12);
        border-radius: 50%;
    }
    .arena-header::after {
        content: '';
        position: absolute;
        bottom: -60px; left: -60px;
        width: 180px; height: 180px;
        background: rgba(255,65,108,.08);
        border-radius: 50%;
    }
    .arena-header-content { position: relative; z-index: 1; }
    .arena-title {
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 6px;
        letter-spacing: -0.5px;
    }
    .arena-subtitle { font-size: .9rem; opacity: .7; margin-bottom: 16px; }
    .countdown-box {
        background: rgba(255,255,255,0.15);
        display: inline-block;
        padding: 10px 28px;
        border-radius: 50px;
        font-size: 1.15rem;
        font-weight: 700;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,0.3);
        letter-spacing: 1px;
        font-family: 'Courier New', monospace;
    }

    /* ── LIST KELOMPOK ─────────────────────────────── */
    .kelompok-list-item {
        display: flex;
        align-items: center;
        padding: 14px 18px;
        border-bottom: 1px solid #f1f1f1;
        gap: 14px;
        transition: background .15s;
    }
    .kelompok-list-item:last-child { border-bottom: none; }
    .kelompok-list-item:hover { background: #f8f9ff; }

    .kl-number {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: #eef0ff;
        color: #6777ef;
        font-weight: 800;
        font-size: 14px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .kl-info { flex-grow: 1; min-width: 0; }
    .kl-name {
        font-size: 14px;
        font-weight: 700;
        color: #1a1a2e;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kl-location {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kl-quota {
        text-align: center;
        min-width: 80px;
        flex-shrink: 0;
    }
    .kl-quota .q-num {
        font-size: 15px;
        font-weight: 800;
        color: #6777ef;
        line-height: 1;
    }
    .kl-quota .q-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #adb5bd;
        margin-top: 2px;
    }

    .kl-bar-wrap {
        width: 80px;
        flex-shrink: 0;
    }
    .kl-bar {
        height: 6px;
        border-radius: 4px;
        background: #e9ecef;
        overflow: hidden;
    }
    .kl-bar-fill {
        height: 100%;
        border-radius: 4px;
        transition: width .4s;
    }
    .kl-bar-fill.ok   { background: linear-gradient(90deg,#47c363,#2f9e44); }
    .kl-bar-fill.warn { background: linear-gradient(90deg,#ffa426,#e67700); }
    .kl-bar-fill.full { background: linear-gradient(90deg,#fc544b,#c92a2a); }

    .btn-join {
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        padding: 7px 18px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .kl-full-badge {
        display: inline-block;
        background: #fff0f0;
        color: #c92a2a;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        flex-shrink: 0;
    }

    /* ── FULL / DISABLED GROUP ──────────────────── */
    .kelompok-list-item.is-disabled {
        opacity: .55;
        pointer-events: none;
    }
    .kelompok-list-item.is-disabled .kl-number {
        background: #f0f0f0;
        color: #999;
    }
    .kelompok-list-item.is-disabled .kl-name {
        color: #999;
    }
    .btn-join-disabled {
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        padding: 7px 18px;
        white-space: nowrap;
        flex-shrink: 0;
        cursor: not-allowed;
    }

    /* ── SECTION DIVIDER ───────────────────────── */
    .kl-section-divider {
        padding: 8px 18px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #adb5bd;
        background: #fafafa;
        border-bottom: 1px solid #f1f1f1;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Pemilihan Kelompok KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('war.index') }}">Plotting KKN</a></div>
            <div class="breadcrumb-item active">Pilih Kelompok</div>
        </div>
    </div>

    <div class="section-body">

        {{-- ── HEADER PLOTTING ──────────────────────────── --}}
        <div class="arena-header">
            <div class="arena-header-content">
                <div class="arena-title">📋 {{ $session->name }}</div>
                <div class="arena-subtitle">
                    Fakultas {{ $peserta->mahasiswa->prodi->fakultas->nama_fakultas }}
                </div>
                <div class="countdown-box" id="countdown">Menghitung waktu...</div>
            </div>
        </div>

        {{-- ── INFO TIPS ──────────────────────────────── --}}
        <div class="alert alert-info d-flex align-items-start shadow-sm mb-3">
            <i class="fas fa-info-circle fa-lg mr-3 mt-1 text-info"></i>
            <div>
                <strong>Petunjuk:</strong> Pilih kelompok yang kuotanya masih tersedia, lalu klik
                <strong>Ambil Kelompok</strong> untuk bergabung. Segera bertindak sebelum kuota habis!
            </div>
        </div>

        {{-- ── DAFTAR KELOMPOK (LIST) ──────────────────── --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-list mr-2 text-primary"></i>
                    Daftar Kelompok — Pilih Kelompok Anda
                </h4>
                @php
                    $availableCount = $kelompoks->filter(fn($k) => $k->status !== 'penuh')->count();
                @endphp
                <span class="badge badge-light" style="font-size:13px;border-radius:20px;padding:6px 14px;" id="kl-total-badge">
                    {{ $availableCount }} / {{ $kelompoks->count() }} tersedia
                </span>
            </div>
            <div class="card-body p-0" id="kelompok-container">
                @php
                    $currentKabupaten = '';
                    $hasFakPenuh = false;
                    $hasFull = false;
                @endphp
                @forelse($kelompoks as $index => $k)
                    @php
                        $filled = $k->pesertaKkn->count();
                        $quota  = $k->kuota;
                        $pct    = $quota > 0 ? round(($filled / $quota) * 100) : 0;
                        $pct    = min($pct, 100);
                        $barClass = $pct >= 100 ? 'full' : ($pct >= 70 ? 'warn' : 'ok');
                        $isFull = $pct >= 100 || $k->status === 'penuh';
                        $canJoin = $k->can_join ?? !$isFull;
                        $kab = $k->desaGelombang->desa->kecamatan->kabupaten ?? '-';
                    @endphp

                    {{-- Kabupaten divider (hanya untuk kelompok tersedia) --}}
                    @if(!$isFull && $kab !== $currentKabupaten)
                        <div class="kl-section-divider" style="font-weight:800;color:#1a1a2e;padding:12px 18px;font-size:13px;">
                            <i class="fas fa-map-marker-alt text-danger mr-2"></i> {{ $kab }}
                        </div>
                        @php $currentKabupaten = $kab; @endphp
                    @endif

                    {{-- Section divider: Kuota Fakultas/Prodi Penuh --}}
                    @if(!$canJoin && !$isFull && !$hasFakPenuh)
                        <div class="kl-section-divider">
                            <i class="fas fa-ban mr-1"></i> Kuota Fakultas/Prodi Anda Penuh di Kelompok Ini
                        </div>
                        @php $hasFakPenuh = true; @endphp
                    @endif

                    {{-- Section divider: Kelompok Penuh --}}
                    @if($isFull && !$hasFull)
                        <div class="kl-section-divider">
                            <i class="fas fa-lock mr-1"></i> Kelompok Penuh / Tidak Tersedia
                        </div>
                        @php $hasFull = true; @endphp
                    @endif

                    <div class="kelompok-list-item {{ !$canJoin || $isFull ? 'is-disabled' : '' }}" id="kl-item-{{ $k->id }}">
                        {{-- Nomor urut --}}
                        <div class="kl-number">{{ $index + 1 }}</div>

                        {{-- Info kelompok --}}
                        <div class="kl-info">
                            <div class="kl-name">{{ $k->nama_kelompok }}</div>
                            <div class="kl-location">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                                {{ $k->desaGelombang->desa->nama_desa ?? '-' }},
                                {{ $k->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}
                                @if(isset($k->desaGelombang->desa->kecamatan->kabupaten))
                                    , {{ $k->desaGelombang->desa->kecamatan->kabupaten }}
                                @endif
                            </div>
                        </div>

                        {{-- Kuota --}}
                        <div class="kl-quota">
                            <div class="q-num" id="quota-{{ $k->id }}">{{ $filled }}/{{ $quota }}</div>
                            <div class="q-label">Peserta</div>
                        </div>

                        {{-- Progress bar --}}
                        <div class="kl-bar-wrap">
                            <div class="kl-bar">
                                <div class="kl-bar-fill {{ $barClass }}" id="kl-bar-{{ $k->id }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <div style="font-size:10px;color:#adb5bd;margin-top:3px;text-align:center;" id="kl-pct-{{ $k->id }}">{{ $pct }}%</div>
                        </div>

                        {{-- Tombol aksi --}}
                        @if($isFull)
                            <div class="kl-full-badge">
                                <i class="fas fa-lock mr-1"></i> Penuh
                            </div>
                        @elseif(!$canJoin)
                            <div class="kl-full-badge" style="background:#fff8e1;color:#e67700;">
                                <i class="fas fa-users-slash mr-1"></i> Fak. Penuh
                            </div>
                        @else
                            <form onsubmit="joinWar(event, {{ $k->id }})" id="form-{{ $k->id }}">
                                <button type="submit" class="btn btn-primary btn-join" id="btn-{{ $k->id }}">
                                    <i class="fas fa-fist-raised mr-1"></i> Ambil
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        Tidak ada kelompok yang tersedia saat ini.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
    const endTime = new Date("{{ $warFaculty->end_at ?? $session->end_at }}").getTime();
    const KELOMPOK_URL = "{{ route('war.kelompoks', $session) }}";

    // Hitung Mundur Sesi Plotting
    setInterval(function() {
        const now      = new Date().getTime();
        const distance = endTime - now;

        if (distance < 0) {
            document.getElementById("countdown").innerHTML = "⏱ Sesi Berakhir";
            document.querySelectorAll('.btn-join').forEach(btn => btn.disabled = true);
            return;
        }

        const hours   = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown").innerHTML =
            (hours > 0 ? String(hours).padStart(2,'0') + "j " : "") +
            String(minutes).padStart(2,'0') + "m " +
            String(seconds).padStart(2,'0') + "d tersisa";
    }, 1000);

    // Fungsi Bergabung Kelompok
    function joinWar(e, kelompokId) {
        e.preventDefault();
        const btn = document.getElementById('btn-' + kelompokId);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';

        fetch(`/war/{{ $session->id }}/join/${kelompokId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(res => {
            if (res.status === 200 && res.body.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Kamu berhasil bergabung ke kelompok.',
                    confirmButtonColor: '#6777ef'
                }).then(() => {
                    window.location.href = "{{ route('war.joined', $session->id) }}";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: res.body.message || 'Terjadi kesalahan. Coba lagi.',
                    confirmButtonColor: '#6777ef'
                });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-fist-raised mr-1"></i> Ambil';
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Bermasalah',
                text: 'Tidak dapat terhubung ke server. Periksa koneksi Anda.',
                confirmButtonColor: '#6777ef'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-fist-raised mr-1"></i> Ambil';
        });
    }

    // Real-time polling refresh daftar kelompok
    function refreshKelompok() {
        fetch(KELOMPOK_URL, { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then(d => {
                var available = 0;
                var total = d.kelompoks.length;

                d.kelompoks.forEach(function(k, idx) {
                    var itemEl  = document.getElementById('kl-item-' + k.id);
                    var quotaEl = document.getElementById('quota-' + k.id);
                    var barEl   = document.getElementById('kl-bar-' + k.id);
                    var pctEl   = document.getElementById('kl-pct-' + k.id);
                    var btnEl   = document.getElementById('btn-' + k.id);
                    var formEl  = document.getElementById('form-' + k.id);
                    var pct = k.kuota > 0 ? Math.round((k.terisi / k.kuota) * 100) : 0;

                    if (quotaEl) quotaEl.textContent = k.terisi + '/' + k.kuota;
                    if (barEl) {
                        barEl.style.width = Math.min(pct, 100) + '%';
                        barEl.className = 'kl-bar-fill ' + (pct >= 100 ? 'full' : pct >= 70 ? 'warn' : 'ok');
                    }
                    if (pctEl) pctEl.textContent = Math.min(pct, 100) + '%';

                    if (k.is_full || k.status === 'penuh') {
                        if (itemEl) itemEl.classList.add('is-disabled');
                        if (btnEl && formEl) {
                            var badge = document.createElement('div');
                            badge.className = 'kl-full-badge';
                            badge.innerHTML = '<i class="fas fa-lock mr-1"></i> Penuh';
                            formEl.replaceWith(badge);
                        }
                    } else if (!k.can_join) {
                        available++;
                        if (itemEl) itemEl.classList.add('is-disabled');
                        if (btnEl && formEl) {
                            var badge = document.createElement('div');
                            badge.className = 'kl-full-badge';
                            badge.style = 'background:#fff8e1;color:#e67700;';
                            badge.innerHTML = '<i class="fas fa-users-slash mr-1"></i> Fak. Penuh';
                            formEl.replaceWith(badge);
                        }
                    } else {
                        available++;
                        if (itemEl) itemEl.classList.remove('is-disabled');
                    }
                });

                var badgeEl = document.getElementById('kl-total-badge');
                if (badgeEl) badgeEl.textContent = available + ' / ' + total + ' tersedia';
            })
            .catch(function() {});
    }

    setInterval(refreshKelompok, 5000);
</script>
@endpush
