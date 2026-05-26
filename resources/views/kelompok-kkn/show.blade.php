@extends('layouts.app')

@section('title', 'Detail Kelompok KKN')

@section('content')
<section class="section">

    {{-- HEADER --}}
    <div class="section-header d-flex justify-content-between align-items-center">

        <div>
            <h1 class="mb-0">
                {{ $kelompok_kkn->nama_kelompok }}
            </h1>

            <small class="text-muted">
                {{ $kelompok_kkn->kode_kelompok }}
            </small>
        </div>

        <div class="d-flex align-items-center">

            @if($kelompok_kkn->status == 'dibuka')
                <form action="{{ route('kelompok-kkn.tutup', $kelompok_kkn->id) }}"
                      method="POST"
                      class="mr-2">
                    @csrf
                    @method('PUT')

                    <button class="btn btn-danger">
                        <i class="fas fa-lock mr-1"></i>
                        Tutup War
                    </button>
                </form>

            @elseif(!$kelompok_kkn->is_full)
                <form action="{{ route('kelompok-kkn.buka', $kelompok_kkn->id) }}"
                      method="POST"
                      class="mr-2">
                    @csrf
                    @method('PUT')

                    <button class="btn btn-success">
                        <i class="fas fa-lock-open mr-1"></i>
                        Buka War
                    </button>
                </form>
            @endif

            <a href="{{ route('kelompok-kkn.edit', $kelompok_kkn->id) }}"
               class="btn btn-warning mr-2">
                <i class="fas fa-edit mr-1"></i>
                Edit
            </a>

            <a href="{{ route('kelompok-kkn.index') }}"
               class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i>
                Kembali
            </a>

        </div>

    </div>

    <div class="section-body">

        {{-- STATISTIK --}}
        <div class="row">

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Total Anggota</h4>
                        </div>

                        <div class="card-body">
                            {{ $kelompok_kkn->terisi }}/{{ $kelompok_kkn->kuota }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-success">
                        <i class="fas fa-home"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Desa</h4>
                        </div>

                        <div class="card-body">
                            {{ $kelompok_kkn->desaGelombang?->desa?->nama_desa ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-warning">
                        <i class="fas fa-user-tie"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>DPL</h4>
                        </div>

                        <div class="card-body">
                            {{ $kelompok_kkn->dosenPembimbingLapangan?->user?->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-statistic-1 shadow-sm">
                    <div class="card-icon bg-danger">
                        <i class="fas fa-chart-pie"></i>
                    </div>

                    <div class="card-wrap">
                        <div class="card-header">
                            <h4>Status</h4>
                        </div>

                        <div class="card-body">
                            @if($kelompok_kkn->is_full)
                                <span class="badge badge-danger">Penuh</span>
                            @elseif($kelompok_kkn->status == 'dibuka')
                                <span class="badge badge-success">Dibuka</span>
                            @elseif($kelompok_kkn->status == 'ditutup')
                                <span class="badge badge-dark">Ditutup</span>
                            @else
                                <span class="badge badge-secondary">Draft</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- PROGRESS --}}
        @php $persentase = $kelompok_kkn->kuota > 0 ? ($kelompok_kkn->terisi / $kelompok_kkn->kuota) * 100 : 0; @endphp
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Kapasitas Kelompok</strong>
                    <strong>{{ $kelompok_kkn->terisi }}/{{ $kelompok_kkn->kuota }}</strong>
                </div>
                <div class="progress" style="height:20px;">
                    <div class="progress-bar {{ $kelompok_kkn->is_full ? 'bg-danger' : 'bg-primary' }}" style="width:{{ min($persentase,100) }}%">{{ round($persentase) }}%</div>
                </div>
            </div>
        </div>

        {{-- NAV TABS --}}
        <div class="group-nav mb-3">
            <a class="active" data-tab="admin-proposal"><i class="fas fa-file-alt mr-1"></i> Proposal</a>
            <a data-tab="admin-anggota"><i class="fas fa-users mr-1"></i> Anggota</a>
            <a data-tab="admin-status"><i class="fas fa-tasks mr-1"></i> Status</a>
            <a data-tab="admin-tugas"><i class="fas fa-upload mr-1"></i> Tugas</a>
            <a data-tab="admin-logbook"><i class="fas fa-book mr-1"></i> Log Book</a>
            <a data-tab="admin-penilaian"><i class="fas fa-star mr-1"></i> Penilaian</a>
        </div>

        {{-- TAB: PROPOSAL --}}
        <div class="tab-content active" id="tab-admin-proposal">
            @if($proposal)
            <div class="card">
                <div class="card-header"><h5>Proposal — <span class="badge badge-{{ $proposal->status==='disetujui'?'success':($proposal->status==='ditolak'?'danger':'info') }}">{{ $proposal->status }}</span></h5></div>
                <div class="card-body">
                    @foreach(['pendahuluan'=>'Pendahuluan','tujuan'=>'Tujuan','manfaat'=>'Manfaat','hasil_observasi'=>'Hasil Observasi','rancangan_program'=>'Rancangan Program','solusi_ide'=>'Solusi & Ide'] as $f=>$l)
                    <h6 class="font-weight-bold text-primary">{{ $l }}</h6>
                    <p style="font-size:13px;text-align:justify;">{!! $proposal->$f ?: '<span class="text-muted">—</span>' !!}</p>
                    @endforeach
                </div>
            </div>
            @else
            <div class="card"><div class="card-body text-center py-4 text-muted">Belum ada proposal.</div></div>
            @endif
        </div>

        {{-- TAB: ANGGOTA --}}
        <div class="tab-content" id="tab-admin-anggota">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center"><h4 class="mb-0 mr-3">Anggota Kelompok</h4><span class="badge badge-primary">{{ $kelompok_kkn->terisi }} Anggota</span></div>
                    @if(!$kelompok_kkn->is_full)<a href="{{ route('kelompok-kkn.anggota.create', $kelompok_kkn->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-user-plus mr-1"></i> Tambah Anggota</a>@endif
                </div>
                <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
                    <thead><tr><th width="60">No</th><th>Nama</th><th>NPM</th><th>Fakultas</th><th>Program Studi</th><th width="170">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($kelompok_kkn->pesertaKkn as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->mahasiswa?->user?->name ?? '-' }}
                                @if($item->id === $kelompok_kkn->ketua_peserta_id)<span class="badge badge-warning ml-1" style="font-size:10px;border-radius:8px;">Ketua</span>@endif</td>
                            <td>{{ $item->mahasiswa?->npm ?? '-' }}</td>
                            <td>{{ $item->mahasiswa?->prodi?->fakultas?->nama_fakultas ?? '-' }}</td>
                            <td>{{ $item->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                            <td><div class="d-flex gap-1">
                                @if($item->id !== $kelompok_kkn->ketua_peserta_id)
                                <form action="{{ route('kelompok-kkn.ketua', ['kelompok_kkn'=>$kelompok_kkn->id,'peserta'=>$item->id]) }}" method="POST" onsubmit="return confirm('Jadikan ketua?')">@csrf @method('PUT')<button type="submit" class="btn btn-warning btn-sm" title="Jadikan Ketua"><i class="fas fa-crown"></i></button></form>
                                @endif
                                <form action="{{ route('kelompok-kkn.anggota.destroy', ['kelompok_kkn'=>$kelompok_kkn->id,'peserta'=>$item->id]) }}" method="POST" onsubmit="return confirm('Keluarkan?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>
                            </div></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada anggota.</td></tr>
                        @endforelse
                    </tbody>
                </table></div></div>
            </div>
        </div>

        {{-- TAB: STATUS --}}
        <div class="tab-content" id="tab-admin-status">
            <div class="card"><div class="card-body">
                <div class="d-flex justify-content-center flex-wrap gap-1 mb-3">
                    @foreach($statusStages as $i => $s)
                    <div class="text-center px-2" style="min-width:85px;">
                        <div style="width:30px;height:30px;border-radius:50%;margin:0 auto 3px;background:{{ $i <= (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#e0e0e0' }};color:{{ $i <= (int)$kelompok_kkn->status_tahap ? '#fff' : '#999' }};font-weight:800;font-size:12px;line-height:30px;">{{ $i }}</div>
                        <small style="font-size:9px;color:{{ $i === (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#adb5bd' }};">{{ $s['nama'] }}</small>
                    </div>
                    @if($i < 7)<div style="width:20px;height:2px;background:{{ $i < (int)$kelompok_kkn->status_tahap ? '#6777ef' : '#e0e0e0' }};margin-top:14px;flex-shrink:0;"></div>@endif
                    @endforeach
                </div>
                <div class="alert alert-primary text-center mb-3"><strong>Tahap Saat Ini: {{ $statusCurrent['nama'] }}</strong></div>
                <form action="{{ route('kelompok.status.change', $kelompok_kkn->id) }}" method="POST" class="form-inline gap-2 justify-content-center">
                    @csrf
                    <select name="stage" class="form-control form-control-sm"><option value="">Ubah Status</option>
                    @foreach($statusStages as $i => $s)<option value="{{ $i }}" {{ $i===(int)$kelompok_kkn->status_tahap?'disabled':'' }}>{{ $i }} - {{ $s['nama'] }}</option>@endforeach</select>
                    <input name="keterangan" class="form-control form-control-sm" placeholder="Keterangan">
                    <button class="btn btn-primary btn-sm" onclick="return confirm('Ubah?')">Simpan</button>
                </form>
            </div></div>
        </div>

        {{-- TAB: TUGAS --}}
        <div class="tab-content" id="tab-admin-tugas">
            @if($tugasList->count())
            @foreach($tugasList as $kat => $items)
            <div class="card mb-3"><div class="card-header"><h5>{{ ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Lain','laporan'=>'Laporan'][$kat] ?? $kat }}</h5></div>
            <div class="card-body p-0">
                @foreach($items as $t)
                <div class="border-bottom p-3">
                    <strong>{{ $t->nama_tugas }}</strong>
                    @if($t->submissions->count())
                    <table class="table table-sm mt-2"><tr><th>Judul</th><th>Oleh</th><th>Berkas</th><th>Status</th><th>Aksi</th></tr>
                    @foreach($t->submissions as $s)
                    <tr><td>{{ $s->judul }}</td><td>{{ $s->pesertaKkn->mahasiswa->user->name ?? '-' }}</td>
                        <td><a href="{{ asset('storage/'.$s->file_path) }}" target="_blank" class="btn btn-sm btn-link"><i class="fas fa-download"></i></a></td>
                        <td><span class="badge badge-{{ $s->status==='diterima'?'success':($s->status==='ditolak'?'danger':'info') }}">{{ $s->status }}</span></td>
                        <td><form action="{{ route('kelompok.tugas.review', $s->id) }}" method="POST" class="form-inline gap-1">@csrf
                            <input name="komentar_dpl" class="form-control form-control-sm" placeholder="Komentar" style="width:80px;">
                            <button name="status" value="diterima" class="btn btn-sm btn-success">✓</button>
                            <button name="status" value="ditolak" class="btn btn-sm btn-danger">✗</button>
                        </form></td></tr>
                    @endforeach</table>
                    @else <p class="text-muted small">Belum ada pengumpulan</p> @endif
                </div>
                @endforeach
            </div></div>
            @endforeach
            @else <div class="card"><div class="card-body text-center py-4 text-muted">Belum ada tugas.</div></div> @endif
        </div>

        {{-- TAB: LOGBOOK --}}
        <div class="tab-content" id="tab-admin-logbook">
            <div class="card mb-3"><div class="card-body">
                <form id="logbook-filter-form" class="form-inline gap-2" onsubmit="return false;">
                    <label class="mr-2"><strong>Pilih Anggota:</strong></label>
                    <select id="logbook-member-select" class="form-control" onchange="filterLogbook()">
                        <option value="">-- Pilih Anggota --</option>
                        @foreach($kelompok_kkn->pesertaKkn as $p)
                        <option value="lb-{{ $p->id }}">{{ $p->mahasiswa->user->name ?? 'Unknown' }}</option>
                        @endforeach
                    </select>
                </form>
            </div></div>
            @foreach($logbookData as $pesertaId => $entries)
            @php $member = $entries->first()->pesertaKkn->mahasiswa->user; $v = $entries->where('is_validated',true)->count(); @endphp
            <div class="card mb-2 logbook-member-card" id="card-lb-{{ $pesertaId }}" style="display:none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ $member->name ?? 'Unknown' }}</strong>
                    <div><span class="badge badge-{{ $v>=20?'success':'warning' }} mr-2">{{ $v }}/{{ $entries->count() }}</span>
                        <form action="{{ route('kelompok.logbook.validateAll') }}" method="POST" class="d-inline">@csrf<input type="hidden" name="peserta_id" value="{{ $pesertaId }}"><button class="btn btn-sm btn-warning" onclick="return confirm('Validasi semua?')">Validasi Semua</button></form>
                    </div>
                </div>
                <div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>Tanggal</th><th>Judul</th><th>Deskripsi</th><th>Status</th></tr></thead>
                <tbody>@foreach($entries as $lb)<tr><td>{{ $lb->tanggal->format('d M Y') }}</td><td>{{ $lb->judul }}</td><td style="max-width:200px;"><small class="d-block text-truncate">{{ $lb->deskripsi }}</small></td><td>{{ $lb->is_validated?'✅':'⏳' }}</td></tr>@endforeach</tbody></table></div>
            </div>
            @endforeach
        </div>

        {{-- TAB: PENILAIAN --}}
        <div class="tab-content" id="tab-admin-penilaian">
            @if($komponenList->count())
            <div class="card"><div class="card-body p-0"><table class="table table-sm mb-0"><tbody>
                <tr class="bg-light"><td colspan="4"><strong>Dosen Pembimbing Lapangan (DPL)</strong></td></tr>
                @foreach($komponenList->where('kategori','dpl') as $k)
                @php $nilai = $penilaianData[$k->id]->nilai ?? null; @endphp
                <tr><td width="50"><span class="badge badge-secondary">{{ $k->bobot }}%</span></td>
                    <td>{{ $k->nama_komponen }}</td>
                    <td width="80"><strong>{{ $nilai !== null ? number_format($nilai,2) : '-' }}</strong></td>
                    <td width="150"><form action="{{ route('kelompok.penilaian.input') }}" method="POST" class="form-inline gap-1">@csrf
                        <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok_kkn->id }}"><input type="hidden" name="komponen_id" value="{{ $k->id }}">
                        <input type="number" name="nilai" class="form-control form-control-sm" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width:80px;">
                        <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                    </form></td></tr>
                @endforeach
                <tr class="bg-light"><td colspan="4"><strong>LPPM</strong></td></tr>
                @foreach($komponenList->where('kategori','lppm') as $k)
                @php $nilai = $penilaianData[$k->id]->nilai ?? null; @endphp
                <tr><td width="50"><span class="badge badge-secondary">{{ $k->bobot }}%</span></td>
                    <td>{{ $k->nama_komponen }}</td>
                    <td width="80"><strong>{{ $nilai !== null ? number_format($nilai,2) : '-' }}</strong></td>
                    <td width="150"><form action="{{ route('kelompok.penilaian.input') }}" method="POST" class="form-inline gap-1">@csrf
                        <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok_kkn->id }}"><input type="hidden" name="komponen_id" value="{{ $k->id }}">
                        <input type="number" name="nilai" class="form-control form-control-sm" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width:80px;">
                        <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                    </form></td></tr>
                @endforeach
            </tbody></table></div></div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.group-nav a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.group-nav a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.getElementById('tab-' + this.dataset.tab).classList.add('active');
        });
    });
    function filterLogbook() {
        var val = document.getElementById('logbook-member-select').value;
        document.querySelectorAll('.logbook-member-card').forEach(c => c.style.display = 'none');
        if (val) document.getElementById('card-' + val).style.display = 'block';
    }
</script>
@endpush
