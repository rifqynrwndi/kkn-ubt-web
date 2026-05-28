@extends('layouts.app')
@section('title', 'Tugas Kelompok — Admin')
@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <h1>Tugas Kelompok</h1>
        <a href="{{ route('admin.tugas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Tugas
        </a>
    </div>
    <div class="section-body">
        <div class="card">
            <div class="card-body p-0"><div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Nama Tugas</th><th>Kategori</th><th>Kelompok</th><th>Submissions</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($tugasList as $t)
                        <tr>
                            <td><strong>{{ $t['nama_tugas'] }}</strong></td>
                            <td><span class="badge badge-info">{{ ['tugas_kelompok'=>'Tugas Kelompok','luaran_wajib'=>'Luaran Wajib','luaran_lain'=>'Luaran Lain','laporan'=>'Laporan'][$t['kategori']] ?? $t['kategori'] }}</span></td>
                            <td><span class="badge badge-primary">{{ $t['total_kelompok'] }} kelompok</span></td>
                            <td>{{ $t['total_submissions'] }}</td>
                            <td>
                                <form action="{{ route('admin.tugas.destroyByNama') }}" method="POST" onsubmit="return confirm('Hapus tugas ini dari SEMUA kelompok?')">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="nama_tugas" value="{{ $t['nama_tugas'] }}">
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
        </div>
    </div>
</section>
@endsection
