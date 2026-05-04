@extends('layouts.app')

@section('title', 'Lengkapi Biodata')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Lengkapi Biodata Mahasiswa</h1>
    </div>

    <div class="section-body">

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                Link verifikasi berhasil dikirim ke email Anda.
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h4>Data Biodata</h4>
            </div>

            <div class="card-body">

                {{-- EMAIL VERIFICATION SECTION --}}
                <div class="mb-4">
                    <label>Email Akun</label>

                    <div class="d-flex align-items-center flex-wrap">
                        <input type="email"
                               class="form-control"
                               value="{{ auth()->user()->email }}"
                               readonly>

                        @if(auth()->user()->hasVerifiedEmail())
                            <span class="badge badge-success ml-2 mt-2 mt-md-0">
                                <i class="fas fa-check-circle"></i> Terverifikasi
                            </span>
                        @else
                            <span class="badge badge-warning ml-2 mt-2 mt-md-0">
                                <i class="fas fa-exclamation-circle"></i> Belum Verifikasi
                            </span>
                        @endif
                    </div>

                    @unless(auth()->user()->hasVerifiedEmail())
                        <small class="text-danger d-block mt-2">
                            Email belum diverifikasi. Silakan cek inbox/spam email Anda.
                        </small>

                        <form method="POST"
                              action="{{ route('verification.send') }}"
                              class="mt-2">
                            @csrf

                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-paper-plane"></i> Kirim Ulang Email Verifikasi
                            </button>
                        </form>
                    @endunless
                </div>

                {{-- FORM BIODATA --}}
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
                            <select name="prodi_id"
                                    class="form-control @error('prodi_id') is-invalid @enderror"
                                    required>
                                <option value="">Pilih Program Studi</option>

                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}"
                                        {{ old('prodi_id', $mahasiswa->prodi_id) == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                @endforeach
                            </select>

                            @error('prodi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- JENIS KELAMIN --}}
                        <div class="form-group col-md-6">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin"
                                    class="form-control @error('jenis_kelamin') is-invalid @enderror"
                                    required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                    Laki-laki
                                </option>
                                <option value="P" {{ old('jenis_kelamin', $mahasiswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                    Perempuan
                                </option>
                            </select>

                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <i class="fas fa-save"></i> Simpan Biodata
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</section>
@endsection
