@extends('layouts.app')

@section('title', 'DPL Dashboard')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>DPL Dashboard</h1>
        @if($gelombangs->isNotEmpty())
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalDraftArtikelDpl">
            <i class="fas fa-file-archive mr-1"></i> Unduh Draft Artikel
        </button>
        @endif
    </div>

    <div class="section-body">
        @php
            $totalKelompok = $kelompoks->count();
            $proposalPending = $kelompoks->where('status_tahap', 1)->count();
            $aktif = $kelompoks->where('status_tahap', 3)->count();
            $selesai = $kelompoks->where('status_tahap', 4)->count();
            $statusLabels = App\Services\StatusService::STAGES;
        @endphp

        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Kelompok</h4>
                        </div>
                        <div class="card-body">
                            {{ $totalKelompok }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Proposal Pending</h4>
                        </div>
                        <div class="card-body">
                            {{ $proposalPending }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-success">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Aktif KKN</h4>
                        </div>
                        <div class="card-body">
                            {{ $aktif }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Selesai</h4>
                        </div>
                        <div class="card-body">
                            {{ $selesai }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Daftar Kelompok Binaan</h4>
                <div style="min-width:250px;">
                    <input type="text" id="dplSearchInput" class="form-control" placeholder="Cari kelompok, desa...">
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="dplKelompokTable">
                        <thead>
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th>Kelompok</th>
                                <th>Desa</th>
                                <th class="text-center" width="90">Anggota</th>
                                <th class="text-center" width="110">Status WAR</th>
                                <th class="text-center" width="120">Status KKN</th>
                                <th class="text-center" width="70">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kelompoks as $index => $k)
                            @php
                                $statusInfo = $statusLabels[$k->status_tahap] ?? $statusLabels[0];
                                $warStatusMap = [
                                    'draft' => ['color' => 'secondary', 'label' => 'Draft'],
                                    'dibuka' => ['color' => 'success', 'label' => 'Dibuka'],
                                    'ditutup' => ['color' => 'warning', 'label' => 'Ditutup'],
                                    'penuh' => ['color' => 'danger', 'label' => 'Penuh'],
                                ];
                                $warStatus = $warStatusMap[$k->status] ?? ['color' => 'info', 'label' => $k->status];
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><strong>{{ $k->nama_kelompok }}</strong></td>
                                <td>{{ $k->desaGelombang->desa->nama_desa ?? '-' }}</td>
                                <td class="text-center">{{ $k->peserta_kkn_count }} / {{ $k->kuota }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $warStatus['color'] }}">{{ $warStatus['label'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $statusInfo['color'] }}">{{ $statusInfo['nama'] }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dpl.kelompok.show', $k->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Belum ada kelompok binaan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        document.getElementById('dplSearchInput')?.addEventListener('keyup', function() {
            var q = this.value.toLowerCase();
            document.querySelectorAll('#dplKelompokTable tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
        </script>

        @push('modals')
        <div class="modal fade" id="modalDraftArtikelDpl" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-file-archive mr-1"></i> Unduh Draft Artikel</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="font-weight-bold">Gelombang</label>
                            <select id="da-gelombang" class="form-control">
                                <option value="">-- Pilih Gelombang --</option>
                                @foreach($gelombangs as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_gelombang }} ({{ $g->tahun }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="da-empty" class="text-muted text-center py-3">Pilih gelombang untuk melihat ringkasan.</div>

                        <div id="da-summary" class="d-none">
                            <div class="alert alert-info" id="da-stats"></div>
                            <div id="da-warning" class="alert alert-warning d-none"></div>
                            <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                                <table class="table table-sm table-striped mb-0">
                                    <thead style="background:#2D3A8A;">
                                        <tr>
                                            <th class="text-white">Kelompok</th>
                                            <th class="text-white">Judul</th>
                                            <th class="text-white text-center" width="120">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="da-rows"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <a id="da-download" href="#" class="btn btn-primary disabled" aria-disabled="true" style="pointer-events:none;">
                            <i class="fas fa-download mr-1"></i> Unduh ZIP
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            var select = document.getElementById('da-gelombang');
            if (!select) return;

            var summaryUrl = @json(route('dpl.draft-artikel.summary'));
            var summaryBox = document.getElementById('da-summary');
            var emptyBox = document.getElementById('da-empty');
            var statsBox = document.getElementById('da-stats');
            var warningBox = document.getElementById('da-warning');
            var rowsBox = document.getElementById('da-rows');
            var download = document.getElementById('da-download');

            function setDownload(active, href) {
                download.href = active ? href : '#';
                download.classList.toggle('disabled', !active);
                download.setAttribute('aria-disabled', active ? 'false' : 'true');
                download.style.pointerEvents = active ? '' : 'none';
            }

            function addCell(tr, text, className) {
                var td = document.createElement('td');
                td.textContent = text || '-';
                if (className) td.className = className;
                tr.appendChild(td);
                return td;
            }

            select.addEventListener('change', function () {
                var id = this.value;
                rowsBox.innerHTML = '';
                warningBox.classList.add('d-none');
                setDownload(false, '#');

                if (!id) {
                    summaryBox.classList.add('d-none');
                    emptyBox.textContent = 'Pilih gelombang untuk melihat ringkasan.';
                    emptyBox.classList.remove('d-none');
                    return;
                }

                emptyBox.textContent = 'Memuat ringkasan...';
                emptyBox.classList.remove('d-none');

                fetch(summaryUrl + '?gelombang_id=' + encodeURIComponent(id), {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(function (response) {
                        if (!response.ok) throw new Error('Gagal memuat ringkasan. Coba lagi.');
                        return response.json();
                    })
                    .then(function (data) {
                        summaryBox.classList.remove('d-none');
                        emptyBox.classList.toggle('d-none', data.rows.length > 0);
                        if (!data.rows.length) emptyBox.textContent = 'Tidak ada kelompok pada gelombang ini.';

                        statsBox.textContent = data.total_kelompok + ' kelompok · '
                            + data.submitted + ' sudah mengumpulkan · '
                            + data.missing + ' belum · '
                            + data.file_count + ' file (' + data.total_size + ')'
                            + (data.file_hilang > 0 ? ' · ' + data.file_hilang + ' file hilang' : '');

                        data.rows.forEach(function (row) {
                            var tr = document.createElement('tr');
                            addCell(tr, row.kelompok);
                            addCell(tr, row.judul);

                            var td = document.createElement('td');
                            td.className = 'text-center';
                            var badge = document.createElement('span');
                            if (row.catatan === 'OK') {
                                badge.className = 'badge badge-success';
                                badge.textContent = 'OK';
                            } else if (row.catatan === 'Belum mengumpulkan') {
                                badge.className = 'badge badge-danger';
                                badge.textContent = 'Belum';
                            } else {
                                badge.className = 'badge badge-warning';
                                badge.textContent = row.catatan;
                            }
                            td.appendChild(badge);
                            tr.appendChild(td);
                            rowsBox.appendChild(tr);
                        });

                        if (data.over_limit) {
                            warningBox.textContent = 'Ukuran melebihi batas ' + data.max_files
                                + ' file / ' + data.max_size + '. Persempit pilihan atau hubungi admin.';
                            warningBox.classList.remove('d-none');
                        }

                        if (data.file_count > 0 && !data.over_limit) {
                            setDownload(true, data.download_url);
                        }
                    })
                    .catch(function (error) {
                        summaryBox.classList.add('d-none');
                        emptyBox.textContent = error.message;
                        emptyBox.classList.remove('d-none');
                    });
            });
        })();
        </script>
        @endpush
    </div>
</section>
@endsection
