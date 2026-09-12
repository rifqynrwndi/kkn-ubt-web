@extends('layouts.auth')

@section('title', 'Sesi Berakhir')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <div style="font-size: 60px; margin-bottom: 16px;">⏰</div>
        <h3>Sesi Telah Berakhir</h3>
        <p>Sesi Anda telah berakhir karena tidak ada aktivitas dalam waktu lama.<br>Silakan refresh halaman untuk melanjutkan.</p>
    </div>
    <button onclick="location.reload()" class="btn btn-primary w-100 mb-3" id="refresh-btn">
        <i class="fas fa-sync-alt mr-2"></i> Refresh Halaman
    </button>
    <p class="small text-muted text-center">Halaman akan otomatis refresh dalam <span id="countdown">5</span> detik...</p>
</div>
@endsection

@push('scripts')
<script>
    let sec = 5;
    setInterval(() => {
        sec--;
        document.getElementById('countdown').textContent = sec;
        if (sec <= 0) location.reload();
    }, 1000);
</script>
@endpush
