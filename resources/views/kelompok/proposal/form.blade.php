@extends('layouts.app')
@section('title', ($proposal ? 'Edit' : 'Buat') . ' Proposal — ' . $kelompok->nama_kelompok)
@push('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-container { min-height: 150px; font-size: 14px; }
    .section-wrap { margin-bottom: 20px; }
    .section-wrap label { font-weight: 700; margin-bottom: 6px; display: block; }
    .char-counter { font-size: 11px; color: #adb5bd; text-align: right; margin-top: 2px; }
</style>
@endpush

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $proposal ? 'Edit' : 'Buat' }} Proposal</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('kelompok.index') }}">Kelompokku</a></div>
            <div class="breadcrumb-item"><a href="{{ route('kelompok.proposal.index') }}">Proposal</a></div>
            <div class="breadcrumb-item active">{{ $proposal ? 'Edit' : 'Buat' }}</div>
        </div>
    </div>

    <div class="section-body">
        <form action="{{ route('kelompok.proposal.store') }}" method="POST" id="proposal-form">
            @csrf
            <input type="hidden" name="action" id="form-action" value="draft">

            @foreach([
                'pendahuluan' => ['Pendahuluan', 'Latar belakang lokasi KKN dan alasan pemilihan. Minimal 200 karakter.', 200],
                'tujuan' => ['Tujuan', 'Tujuan utama dan hasil yang diharapkan. Minimal 100 karakter.', 100],
                'manfaat' => ['Manfaat', 'Manfaat bagi mahasiswa, masyarakat, dan universitas. Minimal 150 karakter.', 150],
                'hasil_observasi' => ['Hasil Observasi (Opsional)', 'Temuan dan observasi lapangan. Dapat diisi nanti.', 200],
                'rancangan_program' => ['Rancangan Program', 'Program kelompok, program individu, timeline. Minimal 300 karakter.', 300],
                'solusi_ide' => ['Solusi / Ide', 'Solusi untuk masalah yang diidentifikasi dan ide inovasi. Minimal 200 karakter.', 200],
            ] as $field => [$label, $desc, $min])
            <div class="card section-wrap">
                <div class="card-header"><h5 class="mb-0">{{ $label }}</h5></div>
                <div class="card-body">
                    <small class="text-muted d-block mb-2">{{ $desc }}</small>
                    <div class="editor" id="editor-{{ $field }}" data-field="{{ $field }}" data-min="{{ $min }}"></div>
                    <div class="char-counter" id="counter-{{ $field }}">0 / {{ $min }} min</div>
                    <textarea name="{{ $field }}" id="textarea-{{ $field }}" style="display:none;">{{ old($field, $proposal->$field ?? '') }}</textarea>
                    @error($field) <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
            @endforeach

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="saveDraft()">
                    <i class="fas fa-save mr-1"></i> Simpan Draft
                </button>
                <button type="button" class="btn btn-success" onclick="submitProposal()">
                    <i class="fas fa-paper-plane mr-1"></i> Ajukan Proposal
                </button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    const editors = {};
    document.querySelectorAll('.editor').forEach(el => {
        const q = new Quill(el, { theme: 'snow', modules: { toolbar: [
            ['bold','italic','underline'], [{list:'ordered'},{list:'bullet'}], ['clean']
        ]}});
        const field = el.dataset.field;
        const min = parseInt(el.dataset.min);
        const textarea = document.getElementById('textarea-' + field);
        const counter = document.getElementById('counter-' + field);

        if (textarea.value) q.root.innerHTML = textarea.value;

        q.on('text-change', () => {
            const text = q.getText().trim();
            counter.textContent = text.length + ' / ' + min + ' min';
            counter.style.color = text.length >= min ? '#47c363' : '#fc544b';
            textarea.value = q.root.innerHTML;
        });

        q.root.innerHTML = textarea.value;
        editors[field] = q;
    });

    function saveDraft() {
        document.getElementById('form-action').value = 'draft';
        document.getElementById('proposal-form').submit();
    }
    function submitProposal() {
        if (!confirm('Ajukan proposal untuk direview DPL? Setelah diajukan tidak dapat diedit.')) return;
        document.getElementById('form-action').value = 'submit';
        document.getElementById('proposal-form').submit();
    }
</script>
@endpush
