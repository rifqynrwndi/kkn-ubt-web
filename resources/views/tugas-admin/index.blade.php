@extends('layouts.app')
@section('title', 'Template Tugas — Admin')
@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Template Tugas Kelompok</h1>
        <a href="{{ route('admin.tugas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Tugas
        </a>
    </div>
    <div class="section-body">
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
                                <th class="text-white text-center" width="90">Submission</th>
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
