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
                                <img id="current-photo"
                                     src="{{ $mahasiswa?->foto ? storage_url($mahasiswa->foto) : asset('img/avatar/avatar-1.png') }}"
                                     class="rounded-circle shadow"
                                     width="120"
                                     height="120"
                                     style="object-fit: cover;">
                            </div>

                            <input type="file"
                                   id="photo-file-input"
                                   name="foto"
                                   class="d-none"
                                   accept="image/*">

                            <button type="button"
                                    id="change-photo-btn"
                                    class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-camera mr-1"></i> Ganti Foto
                            </button>

                            <small class="text-muted d-block mt-1">
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

                        {{-- TEMPAT LAHIR --}}
                        <div class="form-group col-md-6">
                            <label>Tempat Lahir</label>
                            <input type="text"
                                   name="birth_place"
                                   list="birth-place-list"
                                   class="form-control @error('birth_place') is-invalid @enderror"
                                   value="{{ old('birth_place', $mahasiswa->birth_place) }}"
                                   placeholder="Ketik nama kota/kabupaten"
                                   autocomplete="off"
                                   required>
                            <datalist id="birth-place-list">
                                @php $cities = \App\Helpers\IndonesianCities::all(); @endphp
                                @foreach($cities as $city)
                                    <option value="{{ $city }}">
                                @endforeach
                            </datalist>
                            @error('birth_place')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- TANGGAL LAHIR --}}
                        <div class="form-group col-md-6">
                            <label>Tanggal Lahir</label>
                            <input type="date"
                                   name="birth_date"
                                   class="form-control @error('birth_date') is-invalid @enderror"
                                   value="{{ old('birth_date', $mahasiswa->birth_date ? $mahasiswa->birth_date->format('Y-m-d') : '') }}"
                                   max="{{ date('Y-m-d', strtotime('-15 years')) }}"
                                   required>
                            @error('birth_date')
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

{{-- CROP PHOTO MODAL --}}
<div id="cropPhotoModal" class="crop-modal-overlay">
    <div class="crop-modal-content">
        <h5 class="crop-modal-title"><i class="fas fa-crop-alt mr-2" style="margin-right:10px;"></i>Sesuaikan Foto</h5>
        <div class="crop-modal-image-wrapper">
            <img id="crop-image" src="" style="max-width:100%; display:block;">
        </div>
        <div class="text-center mb-3">
            <label class="text-muted small">Preview:</label><br>
            <img id="crop-preview" src="" class="rounded-circle shadow" width="100" height="100" style="object-fit:cover;">
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" id="crop-cancel-btn" class="btn btn-outline-secondary">
                <i class="fas fa-times mr-1"></i> Batal
            </button>
            <button type="button" id="crop-save-btn" class="btn btn-primary">
                <i class="fas fa-check mr-1"></i> Simpan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        // Auto-scroll to first error on page load
        const firstError = document.querySelector('.is-invalid, .invalid-feedback');
        if (firstError) {
            setTimeout(() => {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }

        // Client-side validation on submit
        $('#biodata-form').on('submit', function (e) {
            const requiredFields = [
                { name: 'npm', label: 'NPM' },
                { name: 'prodi_id', label: 'Program Studi' },
                { name: 'jenis_kelamin', label: 'Jenis Kelamin' },
                { name: 'no_hp', label: 'No HP' },
                { name: 'birth_place', label: 'Tempat Lahir' },
                { name: 'birth_date', label: 'Tanggal Lahir' },
                { name: 'nama_ortu', label: 'Nama Orang Tua' },
                { name: 'no_hp_ortu', label: 'No HP Orang Tua' },
                { name: 'alamat_ortu', label: 'Alamat Orang Tua' }
            ];

            let firstInvalid = null;

            requiredFields.forEach(function (field) {
                const el = $('[name="' + field.name + '"]');
                const val = el.val();
                const errorEl = el.closest('.form-group, .form-group col-md-6, [class*="col-"]').find('.field-error-msg');

                if (!val || val.trim() === '') {
                    el.addClass('is-invalid');
                    if (errorEl.length) {
                        errorEl.text(field.label + ' wajib diisi.').addClass('visible');
                    }
                    if (!firstInvalid) firstInvalid = el;
                } else {
                    el.removeClass('is-invalid');
                    if (errorEl.length) {
                        errorEl.removeClass('visible');
                    }
                }
            });

            if (firstInvalid) {
                e.preventDefault();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                iziToast.error({
                    title: 'Form Tidak Lengkap',
                    message: 'Mohon lengkapi semua field yang ditandai.',
                    position: 'topRight',
                    timeout: 5000
                });
                return false;
            }
        });

        // Clear error on input
        $('#biodata-form input, #biodata-form select, #biodata-form textarea').on('input change', function () {
            $(this).removeClass('is-invalid');
            const errorEl = $(this).closest('[class*="col-"]').find('.field-error-msg');
            if (errorEl.length) errorEl.removeClass('visible');
        });
    });

    function resendVerification() {
        const btn = document.getElementById('resend-verification');

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
<script src="{{ asset('js/profile-photo-crop.js') }}"></script>
@endpush
