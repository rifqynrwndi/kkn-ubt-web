@extends('layouts.app')

@section('title', 'Lengkapi Biodata')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Lengkapi Biodata Mahasiswa</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-header">
                <h4>Data Biodata</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('biodata.update') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- FOTO PROFILE --}}
                        <div class="form-group col-md-12 text-center">
                            <label>Foto Profile</label>

                            <div class="mb-3">
                                <img src="{{ $mahasiswa->foto ? asset('storage/'.$mahasiswa->foto) : asset('img/avatar/avatar-1.png') }}"
                                     class="rounded-circle shadow"
                                     width="120"
                                     height="120"
                                     style="object-fit: cover;">
                            </div>

                            <input type="file"
                                   name="foto"
                                   class="form-control @error('foto') is-invalid @enderror"
                                   accept="image/*">

                            <small class="text-muted">
                                JPG / PNG / JPEG maksimal 2MB
                            </small>

                            @error('foto')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- NPM --}}
                        <div class="form-group col-md-6">
                            <label>NPM</label>
                            <input type="text"
                                name="npm"
                                class="form-control"
                                value="{{ old('npm', $mahasiswa->npm) }}"
                                readonly>
                        </div>

                        {{-- PRODI --}}
                        <div class="form-group col-md-6">
                            <label>Program Studi</label>
                            <input type="hidden" name="prodi_id" value="{{ $mahasiswa->prodi_id }}">

                            <input type="text"
                                class="form-control"
                                value="{{ $mahasiswa->prodi?->nama_prodi }}"
                                readonly>
                        </div>

                        {{-- JENIS KELAMIN --}}
                        <div class="form-group col-md-6">
                            <label>Jenis Kelamin</label>
                            <input type="hidden" name="jenis_kelamin" value="{{ $mahasiswa->jenis_kelamin }}">

                            <input type="text"
                                class="form-control"
                                value="{{ $mahasiswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}"
                                readonly>
                        </div>

                        {{-- NO HP --}}
                        <div class="form-group col-md-6">
                            <label>No HP</label>
                            <input type="text"
                                   name="no_hp"
                                   class="form-control @error('no_hp') is-invalid @enderror"
                                   value="{{ old('no_hp', $mahasiswa->no_hp) }}"
                                   required>

                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- NAMA ORTU --}}
                        <div class="form-group col-md-6">
                            <label>Nama Orang Tua / Wali</label>
                            <input type="text"
                                   name="nama_ortu"
                                   class="form-control @error('nama_ortu') is-invalid @enderror"
                                   value="{{ old('nama_ortu', $mahasiswa->nama_ortu) }}"
                                   required>

                            @error('nama_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- HP ORTU --}}
                        <div class="form-group col-md-6">
                            <label>No HP Orang Tua / Wali</label>
                            <input type="text"
                                   name="no_hp_ortu"
                                   class="form-control @error('no_hp_ortu') is-invalid @enderror"
                                   value="{{ old('no_hp_ortu', $mahasiswa->no_hp_ortu) }}"
                                   required>

                            @error('no_hp_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ALAMAT ORTU --}}
                        <div class="form-group col-md-12">
                            <label>Alamat Orang Tua / Wali</label>
                            <textarea name="alamat_ortu"
                                      rows="4"
                                      class="form-control @error('alamat_ortu') is-invalid @enderror"
                                      required>{{ old('alamat_ortu', $mahasiswa->alamat_ortu) }}</textarea>

                            @error('alamat_ortu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            Simpan Biodata
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
