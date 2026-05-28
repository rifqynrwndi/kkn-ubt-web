@extends('layouts.app')

@section('title', 'Detail Kelompok — ' . $kelompok->nama_kelompok)

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $kelompok->nama_kelompok }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('dpl.kelompok.index') }}">Kelompok Binaan</a></div>
            <div class="breadcrumb-item active">Detail</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header"><h4>Informasi Kelompok</h4></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr><th width="140">Nama Kelompok</th><td>{{ $kelompok->nama_kelompok }}</td></tr>
                            <tr><th>Kode</th><td>{{ $kelompok->kode_kelompok }}</td></tr>
                            <tr><th>Desa</th><td>{{ $kelompok->desaGelombang->desa->nama_desa ?? '-' }}</td></tr>
                            <tr><th>Kecamatan</th><td>{{ $kelompok->desaGelombang->desa->kecamatan->nama_kecamatan ?? '-' }}</td></tr>
                            <tr><th>Kabupaten</th><td>{{ $kelompok->desaGelombang->desa->kecamatan->kabupaten ?? '-' }}</td></tr>
                            <tr><th>Kuota</th><td>{{ $kelompok->pesertaKkn->count() }} / {{ $kelompok->kuota }}</td></tr>
                            <tr><th>Ketua</th><td>{{ $kelompok->ketua?->mahasiswa?->user?->name ?? 'Belum ditentukan' }}</td></tr>
                            <tr><th>Status</th><td><span class="badge badge-{{ $kelompok->status === 'penuh' ? 'danger' : 'success' }}">{{ $kelompok->status }}</span></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Anggota Kelompok</h4>
                        <span class="badge badge-primary">{{ $kelompok->pesertaKkn->count() }} orang</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Nama</th>
                                        <th>NPM</th>
                                        <th>Prodi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kelompok->pesertaKkn as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('dpl.mahasiswa.show', $p->id) }}">
                                                {{ $p->mahasiswa?->user?->name ?? '-' }}
                                            </a>
                                            @if($p->id === $kelompok->ketua_peserta_id)
                                                <span class="badge badge-warning ml-1">Ketua</span>
                                            @endif
                                        </td>
                                        <td>{{ $p->mahasiswa?->npm ?? '-' }}</td>
                                        <td>{{ $p->mahasiswa?->prodi?->nama_prodi ?? '-' }}</td>
                                        <td>
                                            @if($p->status_pendaftaran === 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-warning">{{ $p->status_pendaftaran }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada anggota.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- POST-WAR MODULES --}}
        <div class="row mt-3">
            <div class="col-12">
                <ul class="nav nav-tabs" id="dplTabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#dpl-status">Status</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#dpl-proposal">Proposal</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#dpl-tugas">Tugas</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#dpl-logbook">Log Book</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#dpl-penilaian">Penilaian</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="dpl-status">
                        <div class="card"><div class="card-body">
                            <div class="d-flex justify-content-center flex-wrap gap-1 mb-3">
                                @foreach($statusStages as $i => $s)
                                <div class="text-center px-2" style="min-width:85px;">
                                    <div style="width:30px;height:30px;border-radius:50%;margin:0 auto 3px;background:{{ $i <= (int)$kelompok->status_tahap ? '#6777ef' : '#e0e0e0' }};color:{{ $i <= (int)$kelompok->status_tahap ? '#fff' : '#999' }};font-weight:800;font-size:12px;line-height:30px;">{{ $i }}</div>
                                    <small style="font-size:9px;color:{{ $i === (int)$kelompok->status_tahap ? '#6777ef' : '#adb5bd' }};">{{ $s['nama'] }}</small>
                                </div>
                                @if($i < 7)<div style="width:20px;height:2px;background:{{ $i < (int)$kelompok->status_tahap ? '#6777ef' : '#e0e0e0' }};margin-top:14px;flex-shrink:0;"></div>@endif
                                @endforeach
                            </div>
                            <form action="{{ route('kelompok.status.change', $kelompok->id) }}" method="POST" class="form-inline gap-2 justify-content-center">
                                @csrf
                                <select name="stage" class="form-control form-control-sm"><option value="">Ubah Status</option>
                                @foreach($statusStages as $i => $s)<option value="{{ $i }}" {{ $i===(int)$kelompok->status_tahap?'disabled':'' }}>{{ $i }} - {{ $s['nama'] }}</option>@endforeach
                                </select>
                                <input name="keterangan" class="form-control form-control-sm" placeholder="Keterangan">
                                <button class="btn btn-primary btn-sm" onclick="return confirm('Ubah?')">Simpan</button>
                            </form>
                        </div></div>
                    </div>
                    <div class="tab-pane fade" id="dpl-proposal">
                        @if($proposal)
                        <div class="card"><div class="card-body">
                            <div class="alert alert-{{ $proposal->status==='disetujui'?'success':($proposal->status==='ditolak'?'danger':'info') }} mb-3">Status: {{ $proposal->status }}</div>
                            @foreach(['pendahuluan'=>'Pendahuluan','tujuan'=>'Tujuan','manfaat'=>'Manfaat','hasil_observasi'=>'Hasil Observasi','rancangan_program'=>'Rancangan Program','solusi_ide'=>'Solusi & Ide'] as $f=>$l)
                            <h6 class="font-weight-bold text-primary">{{ $l }}</h6><p style="font-size:13px;">{!! $proposal->$f ?: '<span class="text-muted">—</span>' !!}</p>
                            @endforeach
                            @if($proposal->status==='diajukan')
                            <form action="{{ route('kelompok.proposal.review', $proposal->id) }}" method="POST">
                                @csrf
                                <textarea name="komentar_dpl" class="form-control form-control-sm mb-2" rows="2" placeholder="Komentar..."></textarea>
                                <button name="action" value="setujui" class="btn btn-sm btn-success" onclick="return confirm('Setujui?')">Setujui</button>
                                <button name="action" value="tolak" class="btn btn-sm btn-danger" onclick="return confirm('Tolak?')">Tolak</button>
                            </form>
                            @endif
                        </div></div>
                        @else <div class="card"><div class="card-body text-muted text-center py-4">Belum ada proposal.</div></div> @endif
                    </div>
                    <div class="tab-pane fade" id="dpl-tugas">
                        @if($tugasList->count())
                        <div class="card"><div class="card-body p-0">
                            @foreach($tugasList as $kat => $items)
                            <div class="p-3 border-bottom"><strong>{{ ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Lain','laporan'=>'Laporan'][$kat] ?? $kat }}</strong></div>
                            @foreach($items as $t)
                            <div class="px-3"><strong class="small">{{ $t->nama_tugas }}</strong>
                                @if($t->submissions->count())
                                <table class="table table-sm"><tr><th>Judul</th><th>Oleh</th><th>Status</th><th>Aksi</th></tr>
                                @foreach($t->submissions as $s)
                                <tr><td>{{ $s->judul }}</td><td>{{ $s->pesertaKkn->mahasiswa->user->name ?? '-' }}</td><td><span class="badge badge-{{ $s->status==='diterima'?'success':'info' }}">{{ $s->status }}</span></td>
                                    <td><form action="{{ route('kelompok.tugas.review', $s->id) }}" method="POST" class="form-inline gap-1">@csrf
                                        <input name="komentar_dpl" class="form-control form-control-sm" placeholder="Komentar" style="width:80px;">
                                        <button name="status" value="diterima" class="btn btn-sm btn-success">✓</button>
                                        <button name="status" value="ditolak" class="btn btn-sm btn-danger">✗</button>
                                    </form></td></tr>
                                @endforeach</table>
                                @else <p class="text-muted small px-3 pb-2">Belum ada pengumpulan</p> @endif
                            </div>
                            @endforeach
                            @endforeach
                        </div></div>
                        @else <div class="card"><div class="card-body text-muted text-center py-4">Belum ada tugas.</div></div> @endif
                    </div>
                    <div class="tab-pane fade" id="dpl-logbook">
                        @if($logbookData->count())
                        @foreach($logbookData as $pesertaId => $entries)
                        @php $member = $entries->first()->pesertaKkn->mahasiswa->user; $v = $entries->where('is_validated',true)->count(); @endphp
                        <div class="card mb-2"><div class="card-header d-flex justify-content-between align-items-center">
                            <strong>{{ $member->name ?? 'Unknown' }}</strong>
                            <div><span class="badge badge-{{ $v>=20?'success':'warning' }} mr-2">{{ $v }}/{{ $entries->count() }}</span>
                                <form action="{{ route('kelompok.logbook.validateAll') }}" method="POST" class="d-inline">@csrf
                                    <input type="hidden" name="peserta_id" value="{{ $pesertaId }}">
                                    <button class="btn btn-sm btn-warning" onclick="return confirm('Validasi semua?')">Validasi Semua</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>Tanggal</th><th>Judul</th><th>Deskripsi</th><th>Status</th></tr></thead>
                        <tbody>@foreach($entries as $lb)<tr><td>{{ $lb->tanggal->format('d M Y') }}</td><td>{{ $lb->judul }}</td><td style="max-width:200px;"><small class="d-block text-truncate">{{ $lb->deskripsi }}</small></td><td>{{ $lb->is_validated?'✅':'⏳' }}</td></tr>@endforeach</tbody></table></div></div>
                        @endforeach
                        @else <div class="card"><div class="card-body text-muted text-center py-4">Belum ada log book.</div></div> @endif
                    </div>
                    <div class="tab-pane fade" id="dpl-penilaian">
                        @if($komponenList->count())
                        <div class="card"><div class="card-body p-0"><table class="table table-sm mb-0"><tbody>
                            @foreach($komponenList->where('kategori','dpl') as $k)
                            @php $nilai = $penilaianData[$k->id]->nilai ?? null; @endphp
                            <tr>
                                <td width="50"><span class="badge badge-secondary">{{ $k->bobot }}%</span></td>
                                <td>{{ $k->nama_komponen }}</td>
                                <td width="150">
                                    <form action="{{ route('kelompok.penilaian.input') }}" method="POST" class="form-inline gap-1">@csrf
                                        <input type="hidden" name="kelompok_kkn_id" value="{{ $kelompok->id }}"><input type="hidden" name="komponen_id" value="{{ $k->id }}">
                                        <input type="number" name="nilai" class="form-control form-control-sm" placeholder="0-100" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width:80px;">
                                        <button class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody></table></div></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
