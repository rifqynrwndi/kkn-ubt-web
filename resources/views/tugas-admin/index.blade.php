@extends('layouts.app')
@section('title', 'Tugas Kelompok — Admin')
@section('content')
<section class="section">
    <div class="section-header"><h1>Tugas Kelompok</h1></div>
    <div class="section-body">
        <div class="card mb-3">
            <div class="card-header"><h5>Tambah Tugas ke Banyak Kelompok</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.tugas.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <select name="kategori" class="form-control" required>
                                <option value="tugas_kelompok">Tugas Kelompok</option>
                                <option value="luaran_wajib">Luaran Wajib</option>
                                <option value="luaran_lain">Luaran Lain</option>
                                <option value="laporan">Laporan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input name="nama_tugas" class="form-control" placeholder="Nama tugas..." required>
                        </div>
                        <div class="col-md-4">
                            <select name="kelompok_ids[]" class="form-control" multiple required style="height:38px;" id="kelompok-select">
                                @foreach($kelompoks as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelompok }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Ctrl+Click untuk pilih banyak</small>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus mr-1"></i> Tambah</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nama Tugas</th><th>Kategori</th><th>Kelompok</th><th>Submissions</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($tugasList as $t)
                        <tr>
                            <td>{{ $t->nama_tugas }}</td>
                            <td><span class="badge badge-info">{{ ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Lain','laporan'=>'Laporan'][$t->kategori] ?? $t->kategori }}</span></td>
                            <td><small>{{ $t->kelompokKkn->nama_kelompok ?? '-' }}</small></td>
                            <td>{{ $t->submissions->count() }}</td>
                            <td>
                                <form action="{{ route('admin.tugas.destroy', $t->id) }}" method="POST" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada tugas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
            @if($tugasList->hasPages())<div class="card-footer">{{ $tugasList->links() }}</div>@endif
        </div>
    </div>
</section>
@endsection
