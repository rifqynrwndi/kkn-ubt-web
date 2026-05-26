@extends('layouts.app')

@section('title', 'Tambah DPL')

@section('content')
<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Tambah Dosen Pembimbing Lapangan</h1>

        <a href="{{ route('pembimbing-lapangan.index') }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i>
            Kembali
        </a>
    </div>

    <div class="section-body">

        <div class="card shadow-sm">
            <div class="card-header">
                <h4>Form Data DPL</h4>
            </div>

            <div class="card-body">

                <form action="{{ route('pembimbing-lapangan.update', $dpl->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- NAMA DOSEN --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Dosen</label>

                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $dpl->user->name) }}"
                                       placeholder="Masukkan nama dosen"
                                       required>

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- EMAIL --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>

                                <input type="email"
                                       name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $dpl->user->email) }}"
                                       placeholder="contoh@email.com"
                                       required>

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        {{-- NIDN --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIDN</label>

                                <input type="text"
                                       name="nidn"
                                       class="form-control @error('nidn') is-invalid @enderror"
                                       value="{{ old('nidn', $dpl->nidn) }}"
                                       placeholder="Masukkan NIDN">

                                @error('nidn')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- FAKULTAS --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fakultas</label>

                                <select name="fakultas_id"
                                        class="form-control @error('fakultas_id') is-invalid @enderror">

                                    <option value="">
                                        Pilih Fakultas
                                    </option>

                                    @foreach($fakultas as $item)
                                        <option value="{{ $item->id }}"
                                            {{ old('fakultas_id', $dpl->fakultas_id) == $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_fakultas }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('fakultas_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="row">

                        {{-- PASSWORD BARU --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password Baru <small class="text-muted">(kosongkan jika tidak ingin mengubah)</small></label>
                                <div class="input-group">
                                    <input type="password"
                                           name="password"
                                           id="password-input"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Isi untuk mengganti password DPL">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                                            <i class="fas fa-eye" id="toggle-icon"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- STATUS --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>

                        <select name="status"
                                class="form-control @error('status') is-invalid @enderror"
                                required>

                            <option value="aktif"
                                {{ old('status', $dpl->status) == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>

                            <option value="nonaktif"
                                {{ old('status', $dpl->status) == 'nonaktif' ? 'selected' : '' }}>
                                Nonaktif
                            </option>

                        </select>

                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i>
                            Simpan DPL
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('password-input');
    const icon = document.getElementById('toggle-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
@endpush
