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

                <form action="{{ route('pembimbing-lapangan.store') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="row">

                        {{-- NAMA DOSEN --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nama Dosen</label>

                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
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
                                       value="{{ old('email') }}"
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
                                       value="{{ old('nidn') }}"
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
                                            {{ old('fakultas_id') == $item->id ? 'selected' : '' }}>
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

                     {{-- STATUS --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="">Pilih</option>
                                    <option value="laki_laki" {{ old('jenis_kelamin') == 'laki_laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="perempuan" {{ old('jenis_kelamin') == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. HP</label>
                                <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx">
                                @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" name="foto" class="form-control-file @error('foto') is-invalid @enderror" accept="image/*">
                        @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted d-block mt-1">Format: JPG, PNG. Maks 2MB.</small>
                    </div>

                    <div class="form-group">
                        <label>Status</label>

                        <select name="status"
                                class="form-control @error('status') is-invalid @enderror"
                                required>

                            <option value="aktif"
                                {{ old('status') == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>

                            <option value="nonaktif"
                                {{ old('status') == 'nonaktif' ? 'selected' : '' }}>
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
