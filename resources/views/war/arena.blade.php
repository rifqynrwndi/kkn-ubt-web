@extends('layouts.app')

@section('title', 'Pemilihan Kelompok KKN')

@push('css')
<link rel="stylesheet" href="{{ asset('css/war.css') }}">
<style>
    .btn-join-disabled {
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        padding: 7px 18px;
        white-space: nowrap;
        flex-shrink: 0;
        cursor: not-allowed;
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
                <div class="arena-title">
                    <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                    {{ $session->name }}
                </div>
                <div class="arena-subtitle">
                    Fakultas {{ $peserta->mahasiswa->prodi->fakultas->nama_fakultas }}
                </div>
                <div class="countdown-box" id="countdown" aria-live="polite" aria-label="Waktu tersisa">Menghitung waktu...</div>
            </div>
        </div>

        {{-- ── INFO TIPS ──────────────────────────────── --}}
        <div class="alert alert-info d-flex align-items-start shadow-sm mb-3" role="note">
            <i class="fas fa-info-circle fa-lg mr-3 mt-1 text-info" aria-hidden="true"></i>
            <div>
                <strong>Petunjuk:</strong> Pilih kelompok yang kuotanya masih tersedia, lalu klik
                <strong>Ambil Kelompok</strong> untuk bergabung. Segera bertindak sebelum kuota habis!
            </div>
        </div>

        {{-- ── DAFTAR KELOMPOK (LIST) ──────────────────── --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-list mr-2 text-primary" aria-hidden="true"></i>
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
                        <div class="kl-section-divider kl-kabupaten-divider">
                            <i class="fas fa-map-marker-alt mr-2" aria-hidden="true"></i> {{ $kab }}
                        </div>
                        @php $currentKabupaten = $kab; @endphp
                    @endif

                    {{-- Section divider: Kuota Fakultas/Prodi/Gender Penuh --}}
                    @if(!$canJoin && !$isFull && !$hasFakPenuh)
                        <div class="kl-section-divider">
                            <i class="fas fa-ban mr-1" aria-hidden="true"></i> Kuota Fakultas/Prodi/Gender Anda Penuh di Kelompok Ini
                        </div>
                        @php $hasFakPenuh = true; @endphp
                    @endif

                    {{-- Section divider: Kelompok Penuh --}}
                    @if($isFull && !$hasFull)
                        <div class="kl-section-divider">
                            <i class="fas fa-lock mr-1" aria-hidden="true"></i> Kelompok Penuh / Tidak Tersedia
                        </div>
                        @php $hasFull = true; @endphp
                    @endif

                    <div class="kelompok-list-item {{ !$canJoin || $isFull ? 'is-disabled' : '' }}" id="kl-item-{{ $k->id }}">
                        {{-- Nomor urut --}}
                        <div class="kl-number" aria-hidden="true">{{ $index + 1 }}</div>

                        {{-- Info kelompok --}}
                        <div class="kl-info">
                            <div class="kl-name">{{ $k->nama_kelompok }}</div>
                            <div class="kl-location">
                                <i class="fas fa-map-marker-alt mr-1" aria-hidden="true"></i>
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
                            <div class="kl-bar" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kuota terisi {{ $pct }}%">
                                <div class="kl-bar-fill {{ $barClass }}" id="kl-bar-{{ $k->id }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <div class="kl-pct-label" id="kl-pct-{{ $k->id }}">{{ $pct }}%</div>
                        </div>

                        {{-- Tombol aksi --}}
                        @if($isFull)
                            <div class="kl-full-badge">
                                <i class="fas fa-lock mr-1" aria-hidden="true"></i> Penuh
                            </div>
                        @elseif(!$canJoin)
                            <div class="kl-full-badge fak-penuh">
                                <i class="fas fa-users-slash mr-1" aria-hidden="true"></i> Fak. Penuh
                            </div>
                        @else
                            <form onsubmit="joinWar(event, {{ $k->id }})" id="form-{{ $k->id }}">
                                <button type="submit" class="btn btn-primary btn-join" id="btn-{{ $k->id }}" aria-label="Ambil kelompok {{ $k->nama_kelompok }}">
                                    <i class="fas fa-fist-raised mr-1" aria-hidden="true"></i> Ambil
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2 d-block" aria-hidden="true"></i>
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
            document.getElementById("countdown").innerHTML = '<i class="fas fa-clock" aria-hidden="true"></i> Sesi Berakhir';
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
                iziToast.success({
                    title: 'Berhasil!',
                    message: 'Kamu berhasil bergabung ke kelompok.',
                    position: 'topRight',
                    timeout: 3000,
                    onClosed: function() { window.location.href = "{{ route('war.joined', $session->id) }}"; }
                });
            } else {
                iziToast.error({ title: 'Gagal!', message: res.body.message || 'Terjadi kesalahan. Coba lagi.', position: 'topRight', timeout: 8000 });
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-fist-raised mr-1"></i> Ambil';
            }
        })
        .catch(() => {
            iziToast.error({ title: 'Koneksi Bermasalah', message: 'Tidak dapat terhubung ke server.', position: 'topRight', timeout: 8000 });
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
                            badge.className = 'kl-full-badge fak-penuh';
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

    setInterval(refreshKelompok, 30000);
</script>
@endpush
