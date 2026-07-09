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
    </div>
</section>
@endsection
