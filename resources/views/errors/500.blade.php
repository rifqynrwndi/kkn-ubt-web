@extends('layouts.auth')

@section('title', 'Kesalahan Server')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <div style="font-size: 60px; margin-bottom: 16px; color: var(--war-danger);">
            <i class="fas fa-server"></i>
        </div>
        <h3>500</h3>
        <p class="mb-1" style="font-size: 18px; font-weight: 600;">Kesalahan Server Internal</p>
        <p>Terjadi kesalahan di server. Tim teknis telah diberitahu dan sedang memperbaikinya.</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-primary w-100 mb-3">
        <i class="fas fa-home mr-2"></i> Kembali ke Dashboard
    </a>
    <button onclick="location.reload()" class="btn btn-outline-secondary w-100">
        <i class="fas fa-sync-alt mr-2"></i> Coba Lagi
    </button>
</div>
@endsection
