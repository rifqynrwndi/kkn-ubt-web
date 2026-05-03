@extends('layouts.app')

@section('title', 'Riwayat Notifikasi')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Riwayat Notifikasi</h1>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header justify-content-between">
                <h4>Semua Notifikasi Terkirim</h4>

                <a href="{{ route('notifications.create') }}"
                   class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Kirim Notifikasi
                </a>
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Pesan</th>
                                <th>Penerima</th>
                                <th>Tipe</th>
                                <th>Pengirim</th>
                                <th>Dikirim Pada</th>
                                <th width="90">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($notifications as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>

                                    <td style="max-width:300px;">
                                        {{ $item->message }}
                                    </td>

                                    <td>
                                        @php
                                            $recipients = $item->recipients ?? [];
                                            $isAllMahasiswa = count($recipients) >= \App\Models\User::role('mahasiswa')->count();
                                        @endphp

                                        @if($isAllMahasiswa)
                                            <span class="badge badge-success">
                                                Semua Mahasiswa
                                            </span>
                                        @else
                                            @foreach(array_slice($recipients, 0, 3) as $recipient)
                                                <span class="badge badge-primary mb-1">
                                                    {{ $recipient }}
                                                </span>
                                            @endforeach

                                            @if(count($recipients) > 3)
                                                <button type="button"
                                                        class="badge badge-info border-0"
                                                        data-toggle="modal"
                                                        data-target="#recipientModal{{ $item->id }}">
                                                    +{{ count($recipients) - 3 }} lainnya
                                                </button>

                                                {{-- MODAL --}}
                                                <div class="modal fade"
                                                    id="recipientModal{{ $item->id }}"
                                                    tabindex="-1"
                                                    role="dialog">
                                                    <div class="modal-dialog modal-dialog-scrollable" role="document">
                                                        <div class="modal-content">

                                                            <div class="modal-header">
                                                                <h5 class="modal-title">
                                                                    Daftar Penerima Notifikasi
                                                                </h5>

                                                                <button type="button"
                                                                        class="close"
                                                                        data-dismiss="modal">
                                                                    <span>&times;</span>
                                                                </button>
                                                            </div>

                                                            <div class="modal-body">
                                                                @foreach($recipients as $recipient)
                                                                    <span class="badge badge-primary mb-2">
                                                                        {{ $recipient }}
                                                                    </span>
                                                                @endforeach
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge badge-{{ $item->type }}">
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </td>

                                    <td>{{ $item->sender->name ?? '-' }}</td>

                                    <td>
                                        {{ $item->created_at->format('d M Y H:i') }}
                                    </td>

                                    <td>
                                        <form action="{{ route('notifications.history.destroy', $item->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Hapus notifikasi ini dari riwayat dan semua penerima?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        Belum ada riwayat notifikasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

            </div>

            @if($notifications->hasPages())
                <div class="card-footer text-right">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
