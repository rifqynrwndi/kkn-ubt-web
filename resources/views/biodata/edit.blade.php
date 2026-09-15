@extends('layouts.app')

@section('title', 'Lengkapi Biodata')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Lengkapi Biodata Mahasiswa</h1>
    </div>

    <div class="section-body">

        {{-- EMAIL VERIFICATION CARD --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <label class="text-muted small d-block mb-0">Email Akun</label>
                        <strong>{{ auth()->user()->email }}</strong>

                        @if(auth()->user()->hasVerifiedEmail())
                            <span class="badge badge-success ml-2">
                                <i class="fas fa-check-circle"></i> Terverifikasi
                            </span>
                        @else
                            <span class="badge badge-warning ml-2">
                                <i class="fas fa-exclamation-circle"></i> Belum Verifikasi
                            </span>
                            <div class="small text-muted mt-1">
                                Klik tombol di samping untuk mengirim ulang link verifikasi ke email Anda.
                            </div>
                        @endif
                    </div>

                    @unless(auth()->user()->hasVerifiedEmail())
                        <button type="button"
                                class="btn btn-warning"
                                id="resend-verification"
                                onclick="resendVerification()">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Verifikasi Email
                        </button>
                    @endunless
                </div>
            </div>
        </div>

        {{-- FORM BIODATA --}}
        <div class="card mb-3">
            <div class="card-header">
                <h4>Data Biodata</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('biodata.update') }}"
                      method="POST"
                      enctype="multipart/form-data"
                      id="biodata-form">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- FOTO PROFILE --}}
                        <div class="form-group col-md-12 text-center">
                            <label>Foto Profile</label>

                            <div class="mb-3">
                                <img src="{{ $mahasiswa?->foto ? storage_url($mahasiswa->foto) : asset('img/avatar/avatar-1.png') }}"
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

        {{-- DHS UPLOAD SECTION --}}
        <div class="card" id="dhs-section">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Daftar Hasil Studi (DHS)</h4>
                @if($mahasiswa->dhs_status === 'verified')
                    <span class="badge badge-success" style="font-size:13px;padding:6px 12px;"><i class="fas fa-check-circle mr-1"></i> Terverifikasi</span>
                @elseif($mahasiswa->dhs_status === 'rejected')
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 12px;"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>
                @elseif($mahasiswa->dhs_status === 'pending' && $mahasiswa->dhs_path)
                    <span class="badge badge-warning" style="font-size:13px;padding:6px 12px;"><i class="fas fa-clock mr-1"></i> Menunggu Verifikasi</span>
                @else
                    <span class="badge dhs-badge-secondary" style="font-size:13px;padding:6px 12px;"><i class="fas fa-upload mr-1"></i> Belum Diunggah</span>
                @endif
            </div>

            <div class="card-body">
                {{-- Rejection Note --}}
                @if($mahasiswa->dhs_status === 'rejected' && $mahasiswa->dhs_catatan)
                    <div class="alert alert-danger">
                        <strong>Catatan Admin:</strong><br>
                        {{ $mahasiswa->dhs_catatan }}
                    </div>
                @endif

                {{-- File Uploaded Preview --}}
                @if($mahasiswa->dhs_path && $mahasiswa->dhs_status !== 'verified')
                    <div class="mb-3 p-3 border rounded dhs-file-preview-box">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-pdf text-danger fa-2x" style="margin-right:16px;"></i>
                            <div>
                                <strong>DHS sudah diunggah</strong>
                                <div class="text-muted small">{{ basename($mahasiswa->dhs_path) }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Upload Form --}}
                @if($mahasiswa->dhs_status !== 'verified')
                    <form action="{{ route('profile.dhs.store') }}" method="POST" enctype="multipart/form-data" id="dhs-form">
                        @csrf

                        <div class="form-group">
                            <label for="dhs_file"><strong>Upload DHS (PDF only, maks 2MB)</strong></label>
                            <input type="file"
                                   name="dhs_file"
                                   id="dhs_file"
                                   class="form-control @error('dhs_file') is-invalid @enderror"
                                   accept=".pdf"
                                   required>
                            @error('dhs_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary" id="dhs-submit-btn">
                            <i class="fas fa-upload mr-1"></i> Unggah DHS
                        </button>
                    </form>
                @else
                    <div class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        DHS Anda sudah terverifikasi. Tidak perlu mengunggah ulang.
                    </div>
                @endif

                {{-- Panduan --}}
                <hr>
                <div class="row" style="font-size: 13px;">
                    <div class="col-md-6">
                        <strong class="text-muted">Panduan Upload DHS:</strong>
                        <ul class="list-unstyled mt-1 mb-0">
                            <li><i class="fas fa-file-pdf text-danger mr-1"></i> Format: <strong>PDF only</strong></li>
                            <li><i class="fas fa-weight-hanging text-muted mr-1"></i> Ukuran: <strong>maks 2MB</strong></li>
                            <li><i class="fas fa-check-circle text-success mr-1"></i> Pastikan DHS masih berlaku</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted">&nbsp;</strong>
                        <ul class="list-unstyled mt-1 mb-0">
                            <li><i class="fas fa-eye text-muted mr-1"></i> Isi DHS harus terbaca jelas</li>
                            <li><i class="fas fa-clock text-warning mr-1"></i> Verifikasi oleh admin dalam 1x24 jam</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script>
    function resendVerification() {
        const btn = document.getElementById('resend-verification');
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...';

        fetch('{{ route('verification.send') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(r => r.json().then(d => ({ status: r.status, body: d })).catch(() => ({ status: r.status })))
        .then(res => {
            if (res.status === 200 || res.status === 302) {
                iziToast.success({ title: 'Terkirim!', message: 'Link verifikasi telah dikirim ke {{ auth()->user()->email }}. Silakan cek inbox atau spam Anda.', position: 'topRight', timeout: 8000 });
            } else {
                iziToast.error({ title: 'Gagal', message: 'Gagal mengirim verifikasi. Silakan coba lagi.', position: 'topRight', timeout: 8000 });
            }
        })
        .catch(() => {
            iziToast.error({ title: 'Gagal', message: 'Gagal terhubung ke server.', position: 'topRight', timeout: 8000 });
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Kirim Verifikasi Email';
        });
    }
</script>
@endpush
