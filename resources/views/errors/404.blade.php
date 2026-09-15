@extends('layouts.auth')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <div style="font-size: 60px; margin-bottom: 16px; color: var(--war-danger);">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3>404</h3>
        <p class="mb-1" style="font-size: 18px; font-weight: 600;">Halaman Tidak Ditemukan</p>
        <p>Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-primary w-100 mb-3">
        <i class="fas fa-home mr-2"></i> Kembali ke Dashboard
    </a>
    <button onclick="history.back()" class="btn btn-outline-secondary w-100">
        <i class="fas fa-arrow-left mr-2"></i> Halaman Sebelumnya
    </button>
</div>
@endsection
