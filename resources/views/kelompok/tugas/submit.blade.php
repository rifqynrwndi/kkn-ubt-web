@extends('layouts.app')
@section('title', 'Kumpulkan Tugas')
@section('content')
<section class="section">
    <div class="section-header">
        <h1>Kumpulkan Tugas</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('kelompok.index', ['tab' => 'tugas']) }}">Tugas</a></div>
            <div class="breadcrumb-item active">Kumpulkan</div>
        </div>
    </div>
    <div class="section-body">
        <div class="card">
            <div class="card-header"><h4>Form Pengumpulan Tugas</h4></div>
            <div class="card-body">
                @if($tugasList->sum(fn($g) => $g->count()) > 0)
                <form id="tugas-form" action="{{ route('kelompok.tugas.submit', 0) }}" method="POST" enctype="multipart/form-data" onsubmit="return setAction(this)">
                    @csrf
                    <div class="form-group">
                        <label>Pilih Tugas</label>
                        <select name="tugas_id" id="tugas-select" class="form-control" required>
                            <option value="">-- Pilih Tugas --</option>
                            @foreach($tugasList as $kat => $items)
                            <optgroup label="{{ ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Lain','laporan'=>'Laporan'][$kat] ?? $kat }}">
                                @foreach($items as $t)
                                <option value="{{ $t->id }}">{{ $t->nama_tugas }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Judul Pengumpulan</label>
                        <input name="judul" class="form-control" placeholder="Masukkan judul pengumpulan..." required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi (Opsional)</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Berkas</label>
                        <input type="file" name="file" class="form-control-file" required>
                        <small class="text-muted d-block mt-1">PDF, DOC, DOCX, ZIP, JPG, PNG — Maks 10MB</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success"><i class="fas fa-paper-plane mr-1"></i> Kumpulkan</button>
                        <a href="{{ route('kelompok.index', ['tab' => 'tugas']) }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
                @else
                <div class="text-center py-5">
                    <span style="font-size:48px;">✅</span>
                    <h5>Semua Tugas Sudah Dikumpulkan</h5>
                    <p class="text-muted">Semua tugas yang tersedia sudah diupload. Tunggu review dari DPL atau admin.</p>
                    <a href="{{ route('kelompok.index', ['tab' => 'tugas']) }}" class="btn btn-secondary">Kembali</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
function setAction(form) {
    var val = document.getElementById('tugas-select').value;
    if (!val) { iziToast.warning({title:'Pilih Tugas', message:'Silakan pilih tugas terlebih dahulu.', position:'topRight'}); return false; }
    form.action = "/kelompok/tugas/" + val + "/submit";
    return true;
}
</script>
@endpush
