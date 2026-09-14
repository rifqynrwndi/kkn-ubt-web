@extends('layouts.auth')

@section('title', 'Layanan Tidak Tersedia')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <div style="font-size: 60px; margin-bottom: 16px; color: var(--war-warning);">
            <i class="fas fa-wrench"></i>
        </div>
        <h3>503</h3>
        <p class="mb-1" style="font-size: 18px; font-weight: 600;">Layanan Sedang Dalam Pemeliharaan</p>
        <p>Sistem sedang dalam pemeliharaan terjadwal. Silakan coba lagi beberapa menit lagi.</p>
    </div>
    <button onclick="location.reload()" class="btn btn-primary w-100 mb-3">
        <i class="fas fa-sync-alt mr-2"></i> Muat Ulang Halaman
    </button>
    <p class="small text-muted text-center">Halaman akan otomatis muat ulang dalam <span id="countdown">30</span> detik...</p>
</div>
@endsection

@push('scripts')
<script>
    let sec = 30;
    setInterval(() => {
        sec--;
        const el = document.getElementById('countdown');
        if (el) el.textContent = sec;
        if (sec <= 0) location.reload();
    }, 1000);
</script>
@endpush
