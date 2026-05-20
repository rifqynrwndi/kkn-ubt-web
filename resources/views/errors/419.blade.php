@extends('layouts.app')

@section('title', 'Sesi Berakhir')

@section('content')
<div class="section-body text-center py-5">
    <div style="font-size: 72px; margin-bottom: 16px;">⏰</div>
    <h2 class="font-weight-bold mb-2">Sesi Telah Berakhir</h2>
    <p class="text-muted mb-4">Sesi Anda telah berakhir karena tidak ada aktivitas dalam waktu lama.<br>Silakan refresh halaman untuk melanjutkan.</p>
    <button onclick="location.reload()" class="btn btn-primary btn-lg" id="refresh-btn">
        <i class="fas fa-sync-alt mr-2"></i> Refresh Halaman
    </button>
    <p class="small text-muted mt-3">Halaman akan otomatis refresh dalam <span id="countdown">5</span> detik...</p>
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
