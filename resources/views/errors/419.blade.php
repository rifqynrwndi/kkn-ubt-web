@extends('layouts.auth')

@section('title', 'Sesi Berakhir')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <div style="font-size: 60px; margin-bottom: 16px; color: var(--war-warning);">
            <i class="fas fa-clock"></i>
        </div>
        <h3>Sesi Telah Berakhir</h3>
        <p>Sesi Anda telah berakhir karena tidak ada aktivitas dalam waktu lama.<br>Silakan login kembali untuk melanjutkan.</p>
    </div>
    <a href="{{ route('login') }}" class="btn btn-primary w-100 mb-3" id="login-btn">
        <i class="fas fa-sign-in-alt mr-2"></i> Login Kembali
    </a>
    <p class="small text-muted text-center">Anda akan diarahkan ke halaman login dalam <span id="countdown">5</span> detik...</p>
</div>
@endsection

@push('scripts')
<script>
    let sec = 5;
    const loginUrl = @json(route('login'));

    setInterval(() => {
        sec--;
        const el = document.getElementById('countdown');
        if (el) el.textContent = sec;
        if (sec <= 0) window.location.replace(loginUrl);
    }, 1000);
</script>
@endpush
