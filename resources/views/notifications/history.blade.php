@extends('layouts.app')
@section('title', 'Riwayat Notifikasi')
@section('content')
<section class="section">
    <div class="section-header"><h1>Riwayat Notifikasi</h1></div>
    <div class="section-body">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Judul</th><th>Pesan</th><th>Tipe</th><th>Penerima</th><th>Waktu</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->title }}</td>
                                <td>{{ Str::limit($log->message, 60) }}</td>
                                <td><span class="badge badge-{{ $log->type == 'warning' ? 'warning' : ($log->type == 'danger' ? 'danger' : 'info') }}">{{ $log->type }}</span></td>
                                <td>{{ count(json_decode($log->recipients, true) ?? []) }} orang</td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                                <td>
                                    <form action="{{ route('notifications.admin.history.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat notifikasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
