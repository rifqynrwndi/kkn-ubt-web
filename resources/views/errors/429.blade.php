@extends('layouts.auth')

@section('title', 'Terlalu Banyak Permintaan')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <div style="font-size: 60px; margin-bottom: 16px; color: var(--war-warning);">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h3>429</h3>
        <p class="mb-1" style="font-size: 18px; font-weight: 600;">Terlalu Banyak Permintaan</p>
        <p>Anda melakukan terlalu banyak permintaan dalam waktu singkat.<br>Silakan tunggu sebentar sebelum mencoba lagi.</p>
    </div>
    <button onclick="history.back()" class="btn btn-primary w-100 mb-3">
        <i class="fas fa-arrow-left mr-2"></i) Kembali
    </button>
    <button onclick="location.reload()" class="btn btn-outline-secondary w-100">
        <i class="fas fa-sync-alt mr-2"></i> Coba Lagi
    </button>
</div>
@endsection
