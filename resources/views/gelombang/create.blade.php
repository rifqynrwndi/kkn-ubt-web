@extends('layouts.app')

@section('title', 'Tambah Gelombang')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Tambah Gelombang</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('gelombang.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="form-group col-md-6">
                            <label>Nama Gelombang</label>
                            <input type="text" name="nama_gelombang"
                                   class="form-control @error('nama_gelombang') is-invalid @enderror"
                                   value="{{ old('nama_gelombang') }}">
                            @error('nama_gelombang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tahun</label>
                            <input type="number" name="tahun"
                                   class="form-control @error('tahun') is-invalid @enderror"
                                   value="{{ old('tahun', date('Y')) }}">
                            @error('tahun')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai"
                                   class="form-control @error('tgl_mulai') is-invalid @enderror"
                                   value="{{ old('tgl_mulai') }}">
                            @error('tgl_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="tgl_akhir"
                                   class="form-control @error('tgl_akhir') is-invalid @enderror"
                                   value="{{ old('tgl_akhir') }}">
                            @error('tgl_akhir')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Kuota Laki-laki</label>
                            <input type="number" name="kuota_laki"
                                   class="form-control"
                                   value="{{ old('kuota_laki', 0) }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Kuota Perempuan</label>
                            <input type="number" name="kuota_perempuan"
                                   class="form-control"
                                   value="{{ old('kuota_perempuan', 0) }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="persiapan">Persiapan</option>
                                <option value="pendaftaran">Pendaftaran</option>
                                <option value="berjalan">Berjalan</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>

                        <div class="form-group col-md-12">
                            <label>Link Grup Telegram (opsional)</label>
                            <input type="url" name="telegram_group_url"
                                   class="form-control @error('telegram_group_url') is-invalid @enderror"
                                   value="{{ old('telegram_group_url') }}"
                                   placeholder="https://t.me/...">
                            <small class="text-muted">Masukkan link grup Telegram untuk gelombang ini. Mahasiswa akan melihat banner di dashboard mereka.</small>
                            @error('telegram_group_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <hr>

                    <div class="row">

                        <div class="col-md-12 mb-4">
                            <div class="gombang-doc-card">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="gombang-doc-card-label">
                                            <i class="fas fa-file-alt" aria-hidden="true"></i>
                                            Pengaturan Dokumen
                                        </div>
                                        <div class="gombang-doc-card-desc">
                                            Tentukan dokumen yang diperlukan untuk gelombang ini
                                        </div>
                                    </div>
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="skip_dokumen" value="0">
                                        <input type="checkbox" name="skip_dokumen" value="1"
                                               class="custom-control-input" id="skipDokumen"
                                               {{ old('skip_dokumen') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="skipDokumen"></label>
                                    </div>
                                </div>
                                <div id="skipDokumenInfo" class="gombang-skip-alert d-none">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                                    <strong>Skip Dokumen Aktif</strong> — Mahasiswa tidak perlu upload dokumen. Status langsung disetujui saat mendaftar.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12" id="requiredDocsSection">
                            <div class="gombang-doc-card" style="padding: 0;">
                                <div class="gombang-doc-card-header">
                                    <div class="gombang-doc-card-title">
                                        <i class="fas fa-list-check" aria-hidden="true"></i>
                                        Dokumen Wajib Upload
                                    </div>
                                </div>
                                <div class="gombang-doc-card-body">
                                    <div class="gombang-doc-card-sublabel">
                                        Pilih dokumen yang harus diupload mahasiswa sebelum bisa mendaftar KKN.
                                    </div>
                                    @php
                                        $docLabels = [
                                            'surat_pernyataan' => ['label' => 'Surat Pernyataan KKN', 'icon' => 'fa-file-signature'],
                                            'surat_ortu' => ['label' => 'Surat Keterangan Orang Tua', 'icon' => 'fa-users'],
                                            'surat_vaksin' => ['label' => 'Surat Keterangan Vaksin', 'icon' => 'fa-syringe'],
                                            'surat_dokter' => ['label' => 'Surat Keterangan Dokter', 'icon' => 'fa-user-md'],
                                        ];
                                        $oldDocs = old('required_documents', array_keys($docLabels));
                                    @endphp
                                    <div class="row">
                                        @foreach($docLabels as $key => $doc)
                                            <div class="col-md-6 col-lg-4 mb-2">
                                                <label class="gombang-doc-item {{ in_array($key, $oldDocs) ? 'selected' : '' }}" for="doc_{{ $key }}">
                                                    <input type="checkbox" name="required_documents[]"
                                                           value="{{ $key }}"
                                                           class="d-none"
                                                           id="doc_{{ $key }}"
                                                           {{ in_array($key, $oldDocs) ? 'checked' : '' }}
                                                           onchange="toggleDocCard(this)">
                                                    <i class="fas {{ $doc['icon'] }}" aria-hidden="true"></i>
                                                    <span>{{ $doc['label'] }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('required_documents')
                                        <div class="gombang-doc-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-right">
                        <a href="{{ route('gelombang.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button class="btn btn-primary">
                            Simpan
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const skipToggle = document.getElementById('skipDokumen');
        const docsSection = document.getElementById('requiredDocsSection');
        const skipInfo = document.getElementById('skipDokumenInfo');

        function toggleDocs() {
            const isSkip = skipToggle.checked;
            docsSection.style.display = isSkip ? 'none' : 'block';
            skipInfo.classList.toggle('d-none', !isSkip);
        }

        skipToggle.addEventListener('change', toggleDocs);
        toggleDocs();
    });

    function toggleDocCard(checkbox) {
        const label = checkbox.closest('label');
        label.classList.toggle('selected', checkbox.checked);
    }
</script>
@endpush
@endsection
