@extends('layouts.app')
@section('title', 'Template Tugas — Admin')
@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Template Tugas Kelompok</h1>
        <div class="d-flex align-items-center gap-2">
            @if($selectedGelombang && $draftArtikel)
            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalDraftArtikelAdmin">
                <i class="fas fa-file-archive mr-1"></i> Unduh Draft Artikel (ZIP)
            </button>
            @endif
            @if($selectedGelombang)
            <a href="{{ route('admin.tugas.export', ['gelombang_id' => $selectedGelombang]) }}" class="btn btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </a>
            @endif
            <a href="{{ route('admin.tugas.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Tugas
            </a>
        </div>
    </div>
    <div class="section-body">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <form method="GET" class="row align-items-end">
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label font-weight-bold">Pilih Gelombang</label>
                        <select name="gelombang_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Semua Gelombang --</option>
                            @foreach($gelombangs as $g)
                            <option value="{{ $g->id }}" {{ $g->id == $selectedGelombang ? 'selected' : '' }}>
                                {{ $g->nama_gelombang ?? 'Gelombang ' . $g->id }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h4 class="mb-0">Daftar Template Tugas</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead style="background:#2D3A8A;">
                            <tr>
                                <th class="text-white">Nama Tugas</th>
                                <th class="text-white text-center" width="130">Kategori</th>
                                <th class="text-white text-center" width="90">Kelompok</th>
                                <th class="text-white text-center" width="90">Pengumpulan</th>
                                <th class="text-white text-center" width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tugasList as $t)
                            @php
                                $katLabels = ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Tambahan','laporan'=>'Laporan'];
                                $katBadge = ['tugas_kelompok'=>'primary','luaran_wajib'=>'danger','luaran_lain'=>'warning','laporan'=>'info'];
                            @endphp
                            <tr>
                                <td><strong>{{ $t['nama_tugas'] }}</strong></td>
                                <td class="text-center"><span class="badge badge-{{ $katBadge[$t['kategori']] ?? 'secondary' }}">{{ $katLabels[$t['kategori']] ?? $t['kategori'] }}</span></td>
                                <td class="text-center">{{ $t['total_kelompok'] }}</td>
                                <td class="text-center">{{ $t['total_submissions'] }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.tugas.edit', ['nama_tugas' => $t['nama_tugas']]) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.tugas.destroyByNama') }}" method="POST" onsubmit="return confirm('Hapus dari SEMUA kelompok?')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="nama_tugas" value="{{ $t['nama_tugas'] }}">
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada template tugas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if(isset($rekap) && isset($semuaTasks) && $semuaTasks->unique('nama_tugas')->count() > 0)
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Rekap Pengumpulan Tugas <small class="text-muted">({{ $rekap->count() }} kelompok)</small></h4>
                <div class="input-group input-group-sm" style="max-width:280px;">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-search"></i></span></div>
                    <input type="text" class="form-control" id="rekap-search" placeholder="Cari kelompok..." onkeyup="filterRekap()">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-striped table-hover mb-0" id="rekap-table" style="border-collapse:collapse;">
                        <thead style="background:#2D3A8A;position:sticky;top:0;z-index:1;">
                            <tr>
                                <th class="text-white" width="220" style="cursor:pointer;" onclick="sortRekap('kelompok')">Kelompok <i class="fas fa-sort ml-1"></i></th>
                                @php $ci = 0; @endphp
                                @foreach($semuaTasks->unique('nama_tugas') as $wt)
                                <th class="text-white text-center" width="80" style="font-size:10px;cursor:pointer;" onclick="sortRekap({{ $ci }})">{{ $wt->nama_tugas }} <i class="fas fa-sort ml-1"></i></th>
                                @php $ci++; @endphp
                                @endforeach
                                <th class="text-white text-center" width="70" style="cursor:pointer;" onclick="sortRekap('total')">Total <i class="fas fa-sort ml-1"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekap as $kel)
                            <tr>
                                <td><small>{{ $kel->nama_kelompok }}</small></td>
                                @php $done = 0; @endphp
                                @foreach($semuaTasks->unique('nama_tugas') as $wt)
                                @php
                                    $tugas = $kel->tugasKelompok->firstWhere('nama_tugas', $wt->nama_tugas);
                                    $submitted = $tugas && $tugas->submissions->isNotEmpty();
                                    if($submitted) $done++;
                                @endphp
                                <td class="text-center">
                                    @if($submitted)<span class="badge badge-success">✅</span>
                                    @else<span class="badge badge-danger">❌</span>@endif
                                </td>
                                @endforeach
                                <td class="text-center font-weight-bold">
                                    <span class="badge badge-{{ $done == $semuaTasks->unique('nama_tugas')->count() ? 'success' : 'danger' }}">
                                        {{ $done }}/{{ $semuaTasks->unique('nama_tugas')->count() }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>

    @push('modals')
    @if($selectedGelombang && $draftArtikel)
    <div class="modal fade" id="modalDraftArtikelAdmin" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-archive mr-1"></i> Unduh Draft Artikel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>{{ $draftArtikel['total_kelompok'] }} kelompok</strong> ·
                        {{ $draftArtikel['submitted'] }} sudah mengumpulkan ·
                        {{ $draftArtikel['missing'] }} belum ·
                        <strong>{{ $draftArtikel['file_count'] }} file</strong> ({{ $draftArtikel['total_size'] }})
                        @if($draftArtikel['file_hilang'] > 0)
                            · <span class="text-danger">{{ $draftArtikel['file_hilang'] }} file hilang</span>
                        @endif
                    </div>
                    @if($draftArtikel['over_limit'])
                        <div class="alert alert-warning mb-3">
                            Ukuran melebihi batas {{ $draftArtikel['max_files'] }} file / {{ $draftArtikel['max_size'] }}. Persempit pilihan gelombang atau hubungi admin.
                        </div>
                    @endif
                    @if($draftArtikel['file_count'] === 0)
                        <div class="alert alert-warning mb-3">Belum ada Draft Artikel yang dikumpulkan pada gelombang ini.</div>
                    @endif
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead style="background:#2D3A8A;">
                                <tr>
                                    <th class="text-white">Kelompok</th>
                                    <th class="text-white">DPL</th>
                                    <th class="text-white">Judul</th>
                                    <th class="text-white text-center" width="120">Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($draftArtikel['rows'] as $row)
                                <tr>
                                    <td>{{ $row['kelompok'] }}</td>
                                    <td><small>{{ $row['dpl'] }}</small></td>
                                    <td><small>{{ $row['judul'] ?: '-' }}</small></td>
                                    <td class="text-center">
                                        @if($row['catatan'] === 'OK')
                                            <span class="badge badge-success">OK</span>
                                        @elseif($row['catatan'] === 'Belum mengumpulkan')
                                            <span class="badge badge-danger">Belum</span>
                                        @else
                                            <span class="badge badge-warning">{{ $row['catatan'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @if($draftArtikel['rows']->isEmpty())
                                <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada kelompok pada gelombang ini.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    @if($draftArtikel['file_count'] > 0 && ! $draftArtikel['over_limit'])
                        <a href="{{ route('admin.tugas.draft-artikel.zip', ['gelombang_id' => $selectedGelombang]) }}" class="btn btn-primary">
                            <i class="fas fa-download mr-1"></i> Unduh ZIP
                        </a>
                    @else
                        <button type="button" class="btn btn-primary" disabled>Unduh ZIP</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    @endpush
</section>
@endsection
@push('scripts')
<script>
var rekapSortDir = {};

function filterRekap() {
    var q = (document.getElementById('rekap-search')?.value || '').toLowerCase();
    var tbody = document.querySelector('#rekap-table tbody');
    if (!tbody) return;
    tbody.querySelectorAll('tr').forEach(function(row) {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

function sortRekap(col) {
    var tbody = document.querySelector('#rekap-table tbody');
    if (!tbody) return;
    var rows = Array.from(tbody.querySelectorAll('tr'));
    var dir = rekapSortDir[col] = !rekapSortDir[col];

    rows.sort(function(a, b) {
        var valA, valB;
        var ca = a.querySelectorAll('td');
        var cb = b.querySelectorAll('td');

        if (col === 'kelompok') {
            valA = ca[0].textContent.trim().toLowerCase();
            valB = cb[0].textContent.trim().toLowerCase();
        } else if (col === 'total') {
            valA = parseInt(ca[ca.length-1].textContent.match(/\d+/)?.[0] || 0);
            valB = parseInt(cb[cb.length-1].textContent.match(/\d+/)?.[0] || 0);
        } else {
            valA = ca[col+1].textContent.includes('✅') ? 1 : 0;
            valB = cb[col+1].textContent.includes('✅') ? 1 : 0;
        }

        if (valA < valB) return dir ? 1 : -1;
        if (valA > valB) return dir ? -1 : 1;
        return 0;
    });

    rows.forEach(function(r) { tbody.appendChild(r); });
}
</script>
@endpush
