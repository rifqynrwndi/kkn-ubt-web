@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <h3>Selamat Datang</h3>
        <p>Login untuk mengakses sistem KKN</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email"
                class="form-control @error('email') is-invalid @enderror"
                name="email" value="{{ old('email') }}"
                placeholder="Masukkan email"
                required autofocus>
            @error('email')
                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password"
                class="form-control @error('password') is-invalid @enderror"
                name="password"
                placeholder="Masukkan password"
                required>
            @error('password')
                <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('password.request') }}" class="small">Lupa Password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-sign-in-alt mr-2"></i> Login
        </button>

        <div class="text-center mt-3">
            <span class="small text-muted">Belum punya akun?</span>
            <a href="{{ route('register') }}" class="small font-weight-bold">Register</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
@if(session('success'))
<script>
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    Swal.fire({
        icon: 'success', title: 'Berhasil', text: '{{ session('success') }}',
        confirmButtonColor: '#6777ef',
        background: isDark ? '#1f2430' : '#fff',
        color: isDark ? '#d6d9df' : '#545454',
    });
</script>
@endif
@endpush
