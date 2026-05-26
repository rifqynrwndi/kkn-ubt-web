@extends('layouts.app')
@section('title', 'Kelompok KKN — ' . $kelompok->nama_kelompok)
@push('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .group-header-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 20px; padding: 32px; color: #fff;
        position: relative; overflow: hidden; margin-bottom: 20px;
    }
    .group-header-card::before {
        content: ''; position: absolute; top: -50px; right: -50px;
        width: 200px; height: 200px; background: rgba(255,255,255,.04); border-radius: 50%;
    }
    .group-photo-wrap {
        position: relative; width: 140px; height: 140px; flex-shrink: 0;
        border-radius: 16px; overflow: hidden; border: 3px solid rgba(255,255,255,.3); background: rgba(255,255,255,.1);
    }
    .group-photo-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .group-photo-upload {
        position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,.6);
        padding: 4px; font-size: 11px; text-align: center; cursor: pointer; color: #fff;
    }
    .group-photo-upload:hover { background: rgba(0,0,0,.8); }
    .group-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; margin: 2px 4px; }
    .group-nav {
        display: flex; justify-content: center; gap: 0; background: #fff;
        border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.06);
        overflow: hidden; margin-bottom: 24px; flex-wrap: wrap;
    }
    .group-nav a {
        padding: 14px 20px; text-align: center; font-size: 13px; font-weight: 600;
        color: #6c757d; border-bottom: 3px solid transparent; transition: .2s;
        text-decoration: none; white-space: nowrap; cursor: pointer;
    }
    .group-nav a:hover, .group-nav a.active { color: #6777ef; border-bottom-color: #6777ef; background: #f8f9ff; }
    .group-nav a i { margin-right: 6px; }
    [data-bs-theme="dark"] .group-nav { background: #1f2430; box-shadow: 0 2px 12px rgba(0,0,0,.2); }
    [data-bs-theme="dark"] .group-nav a { color: #aab1c1; }
    [data-bs-theme="dark"] .group-nav a:hover, [data-bs-theme="dark"] .group-nav a.active { background: rgba(103,119,239,.1); }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .quill-editor-readonly .ql-editor { padding: 12px; }
    .quill-editor-readonly .ql-container { min-height: auto; border: none !important; }
    .quill-editor-readonly .ql-toolbar { display: none; }
    .ql-container { min-height: 150px; font-size: 14px; }
    .section-wrap { margin-bottom: 20px; }
    .section-wrap label { font-weight: 700; margin-bottom: 6px; display: block; }
    .char-counter { font-size: 11px; color: #adb5bd; text-align: right; margin-top: 2px; }
    /* Proposal Document Style */
    .proposal-doc {
        background: #fff; border-radius: 12px; padding: 48px 40px;
        box-shadow: 0 2px 16px rgba(0,0,0,.06); max-width: 860px; margin: 0 auto;
    }
    .proposal-doc-header { text-align: center; border-bottom: 2px solid #0f3460; padding-bottom: 24px; margin-bottom: 28px; }
    .proposal-doc-header h3 { font-size: 1.1rem; font-weight: 700; color: #0f3460; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .5px; }
    .proposal-doc-header h2 { font-size: 1.3rem; font-weight: 800; color: #1a1a2e; margin: 8px 0; }
    .proposal-doc-header .doc-meta { font-size: .85rem; color: #6c757d; text-align: center; }
    .proposal-doc-body h4 { font-size: 1rem; font-weight: 700; color: #0f3460; margin: 24px 0 10px; text-transform: uppercase; text-align: center; letter-spacing: .3px; }
    .proposal-doc-body p { text-align: justify; line-height: 1.8; margin-bottom: 20px; font-size: .9rem; }
    [data-bs-theme="dark"] .proposal-doc { background: #1f2430; }
    [data-bs-theme="dark"] .proposal-doc-header { border-bottom-color: #374151; }
    [data-bs-theme="dark"] .proposal-doc-header h3, [data-bs-theme="dark"] .proposal-doc-body h4 { color: #a4b0f5; }
    [data-bs-theme="dark"] .proposal-doc-header h2 { color: #f1f3f8; }
    [data-bs-theme="dark"] .proposal-doc-header .doc-meta { color: #aab1c1; }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Kelompok KKN</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Kelompokku</div>
        </div>
    </div>

    <div class="section-body">

        {{-- GROUP HEADER --}}
        <div class="group-header-card">
            <div class="d-flex align-items-start flex-wrap gap-3" style="position:relative;z-index:1;">
                <div class="group-photo-wrap">
                    @if($kelompok->foto_kelompok)
                        <img src="{{ asset('storage/'.$kelompok->foto_kelompok) }}" alt="Foto Kelompok">
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100" style="font-size:40px;">👥</div>
                    @endif
                    @if($isKetua)
                        <label class="group-photo-upload" for="photo-input"><i class="fas fa-camera mr-1"></i> Ubah</label>
                        <form action="{{ route('kelompok.upload-photo') }}" method="POST" enctype="multipart/form-data" style="display:none;">
                            @csrf
                            <input type="file" name="foto_kelompok" id="photo-input" accept="image/*" onchange="this.form.submit()">
                        </form>
                    @endif
                </div>
                <div>
                    <div class="mb-2">
                        <span class="group-badge" style="background:rgba(255,255,255,.15);">{{ $kelompok->desaGelombang->gelombang->nama_gelombang ?? 'KKN' }}</span>
                        <span class="group-badge" style="background:rgba(103,119,239,.3);">{{ $kelompok->kode_kelompok }}</span>
                        <span class="group-badge" style="background:rgba(71,195,99,.3);">Status: {{ $kelompok->status }}</span>
                    </div>
                    <h2 style="font-weight:800;margin-bottom:6px;font-size:1.5rem;">{{ $kelompok->nama_kelompok }}</h2>
                    <div style="opacity:.75;font-size:.85rem;">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ $kelompok->desaGelombang->desa->nama_desa ?? '-' }}, {{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}, {{ $kelompok->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}
                    </div>
                    <div style="opacity:.6;font-size:.8rem;margin-top:2px;">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ \Carbon\Carbon::parse($kelompok->desaGelombang->gelombang->tgl_mulai ?? now())->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($kelompok->desaGelombang->gelombang->tgl_akhir ?? now())->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- NAVIGATION TABS --}}
        <div class="group-nav">
            <a class="active" data-tab="dashboard"><i class="fas fa-home"></i> Dashboard</a>
            <a data-tab="proposal"><i class="fas fa-file-alt"></i> Proposal</a>
            <a data-tab="status"><i class="fas fa-tasks"></i> Status</a>
            <a data-tab="peserta"><i class="fas fa-users"></i> Peserta & DPL</a>
            <a data-tab="tugas"><i class="fas fa-upload"></i> Tugas</a>
            <a data-tab="logbook"><i class="fas fa-book"></i> Log Book</a>
            <a data-tab="penilaian"><i class="fas fa-star"></i> Penilaian</a>
        </div>

        {{-- TAB: DASHBOARD --}}
        <div class="tab-content active" id="tab-dashboard">
            <div class="card">
                <div class="card-body text-center py-5">
                    <span style="font-size:48px;display:block;margin-bottom:12px;">🏠</span>
                    <h5>Selamat Datang di Kelompok KKN</h5>
                    <p class="text-muted">Gunakan menu navigasi di atas untuk mengakses Proposal, Status, Log Book, dan fitur lainnya.</p>
                </div>
            </div>
        </div>

        {{-- TAB: PROPOSAL --}}
        <div class="tab-content" id="tab-proposal">
            @php
                $fields = ['pendahuluan' => 'Pendahuluan', 'tujuan' => 'Tujuan', 'manfaat' => 'Manfaat', 'hasil_observasi' => 'Hasil Observasi', 'rancangan_program' => 'Rancangan Program', 'solusi_ide' => 'Solusi & Ide'];
            @endphp

            @if($proposal)
                <div class="alert alert-{{ $proposal->status === 'disetujui' ? 'success' : ($proposal->status === 'ditolak' ? 'danger' : 'info') }} mb-3">
                    <strong>Status:</strong>
                    @if($proposal->status === 'draft') Draft
                    @elseif($proposal->status === 'diajukan') Diajukan — Menunggu review DPL
                    @elseif($proposal->status === 'disetujui') Disetujui
                    @else Ditolak
                    @endif
                    @if($proposal->status === 'ditolak' && $proposal->komentar_dpl)
                        <br><small><strong>Komentar DPL:</strong> {{ $proposal->komentar_dpl }}</small>
                    @endif
                </div>
            @endif

            @if($isKetua && (!$proposal || in_array($proposal->status ?? '', ['draft', 'ditolak'])))
                <button class="btn btn-primary mb-3" onclick="toggleProposalEdit()">
                    <i class="fas fa-edit mr-1"></i> {{ $proposal && $proposal->status !== 'ditolak' ? 'Edit Proposal' : 'Buat Proposal' }}
                </button>
            @endif

            {{-- EDIT FORM --}}
            @if($isKetua)
            <div id="proposal-form-wrap" style="display:none;">
                <form action="{{ route('kelompok.proposal.store') }}" method="POST" id="proposal-form">
                    @csrf
                    <input type="hidden" name="action" id="form-action" value="draft">
                    @foreach(['pendahuluan' => ['Pendahuluan', 200], 'tujuan' => ['Tujuan', 100], 'manfaat' => ['Manfaat', 150], 'hasil_observasi' => ['Hasil Observasi (Opsional)', 200], 'rancangan_program' => ['Rancangan Program', 300], 'solusi_ide' => ['Solusi / Ide', 200]] as $field => [$label, $min])
                    <div class="card section-wrap">
                        <div class="card-header"><h5 class="mb-0">{{ $label }}</h5></div>
                        <div class="card-body">
                            <small class="text-muted d-block mb-2">Minimal {{ $min }} karakter</small>
                            <div class="editor" data-field="{{ $field }}" data-min="{{ $min }}"></div>
                            <div class="char-counter" id="counter-{{ $field }}">0 / {{ $min }} min</div>
                            <textarea name="{{ $field }}" id="textarea-{{ $field }}" style="display:none;">{{ old($field, $proposal->$field ?? '') }}</textarea>
                            @error($field) <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('form-action').value='draft';document.getElementById('proposal-form').submit()"><i class="fas fa-save mr-1"></i> Simpan Draft</button>
                        <button type="button" class="btn btn-success" onclick="if(confirm('Ajukan untuk review DPL?')){document.getElementById('form-action').value='submit';document.getElementById('proposal-form').submit()}"><i class="fas fa-paper-plane mr-1"></i> Ajukan</button>
                    </div>
                </form>
            </div>
            @endif

            {{-- READ VIEW — always shown --}}
            <div id="proposal-read-view" class="proposal-doc">
                <div class="proposal-doc-header">
                    <h3>Proposal Program Kerja KKN</h3>
                    <h2>{{ $kelompok->nama_kelompok }}</h2>
                    <div class="doc-meta">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ $kelompok->desaGelombang->desa->nama_desa ?? '-' }},
                        {{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }},
                        {{ $kelompok->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}
                        &nbsp;&middot;&nbsp;
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ \Carbon\Carbon::parse($kelompok->desaGelombang->gelombang->tgl_mulai ?? now())->format('d M Y') }}
                        &mdash;
                        {{ \Carbon\Carbon::parse($kelompok->desaGelombang->gelombang->tgl_akhir ?? now())->format('d M Y') }}
                    </div>
                </div>
                <div class="proposal-doc-body">
                    @foreach($fields as $field => $label)
                    <h4>{{ $label }}</h4>
                    @php
                        $content = $proposal->$field ?? null;
                        $isEmpty = !$content || trim(strip_tags($content)) === '';
                    @endphp
                    <p>{!! $isEmpty ? '<span class="text-muted">—</span>' : $content !!}</p>
                    @endforeach
                </div>
            </div>

            {{-- DPL REVIEW --}}
            @if($isDpl && $proposal && $proposal->status === 'diajukan')
            <div class="card mt-3">
                <div class="card-header"><h4>Review (DPL)</h4></div>
                <div class="card-body">
                    <form action="{{ route('kelompok.proposal.review', $proposal->id) }}" method="POST">
                        @csrf
                        <div class="form-group"><label>Komentar</label><textarea name="komentar_dpl" class="form-control" rows="3"></textarea></div>
                        <button type="submit" name="action" value="setujui" class="btn btn-success" onclick="return confirm('Setujui?')"><i class="fas fa-check mr-1"></i> Setujui</button>
                        <button type="submit" name="action" value="tolak" class="btn btn-danger" onclick="return confirm('Tolak?')"><i class="fas fa-times mr-1"></i> Tolak</button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        {{-- TAB: STATUS --}}
        <div class="tab-content" id="tab-status">
            <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">🚧</span><h5>Status — Segera Hadir</h5></div></div>
        </div>

        {{-- TAB: PESERTA --}}
        <div class="tab-content" id="tab-peserta">
            <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">🚧</span><h5>Peserta & DPL — Segera Hadir</h5></div></div>
        </div>

        {{-- TAB: TUGAS --}}
        <div class="tab-content" id="tab-tugas">
            <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">🚧</span><h5>Tugas — Segera Hadir</h5></div></div>
        </div>

        {{-- TAB: LOGBOOK --}}
        <div class="tab-content" id="tab-logbook">
            <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">🚧</span><h5>Log Book — Segera Hadir</h5></div></div>
        </div>

        {{-- TAB: PENILAIAN --}}
        <div class="tab-content" id="tab-penilaian">
            <div class="card"><div class="card-body text-center py-5"><span style="font-size:48px;">🚧</span><h5>Penilaian — Segera Hadir</h5></div></div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    // Tab switching
    document.querySelectorAll('.group-nav a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.group-nav a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + this.dataset.tab).classList.add('active');
        });
    });

    // Proposal form toggle
    function toggleProposalEdit() {
        const form = document.getElementById('proposal-form-wrap');
        const read = document.getElementById('proposal-read-view');
        const btn = document.querySelector('[onclick="toggleProposalEdit()"]');
        const isOpen = form.style.display === 'block';
        form.style.display = isOpen ? 'none' : 'block';
        if (read) read.style.display = isOpen ? 'block' : 'none';
        if (btn) btn.innerHTML = isOpen ? '<i class="fas fa-edit mr-1"></i> Edit Proposal' : '<i class="fas fa-times mr-1"></i> Tutup Editor';
        if (!isOpen) initQuillEditors();
    }

    // Quill editors
    let quillInit = false;
    function initQuillEditors() {
        if (quillInit) return; quillInit = true;
        document.querySelectorAll('.editor').forEach(el => {
            const field = el.dataset.field, min = parseInt(el.dataset.min);
            const textarea = document.getElementById('textarea-' + field);
            const counter = document.getElementById('counter-' + field);
            const q = new Quill(el, { theme: 'snow', modules: { toolbar: [['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['clean']] }});
            if (textarea.value) q.root.innerHTML = textarea.value;
            q.on('text-change', () => {
                const text = q.getText().trim();
                counter.textContent = text.length + ' / ' + min + ' min';
                counter.style.color = text.length >= min ? '#47c363' : '#fc544b';
                textarea.value = q.root.innerHTML;
            });
        });
    }

    // Open proposal tab if there's an error or after submission
    @if($errors->any() || request('tab') === 'proposal')
        document.querySelector('[data-tab="proposal"]').click();
        @if($errors->any())
            setTimeout(() => toggleProposalEdit(), 200);
        @endif
    @endif
</script>
@endpush
