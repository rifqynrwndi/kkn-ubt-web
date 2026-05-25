@extends('layouts.app')

@section('title', 'Riwayat Notifikasi')

@section('content')

<style>
    .table td,
    .table th {
        vertical-align: middle !important;
    }

    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .message-column {
        white-space: normal !important;
        min-width: 250px;
        max-width: 350px;
        word-break: break-word;
        line-height: 1.5;
    }

    .recipient-column {
        min-width: 220px;
    }

    .action-column {
        white-space: nowrap !important;
        min-width: 90px;
    }

    .badge-custom {
        padding: 6px 10px;
        font-size: 12px;
        border-radius: 6px;
    }

    .recipient-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
</style>

<section class="section">

    <div class="section-header d-flex justify-content-between align-items-center">

        <h1>Riwayat Notifikasi</h1>

        <div class="d-flex">

            <form action="{{ route('notifications.mark-all-read') }}"
                  method="POST"
                  class="mr-2">
                @csrf
                <button class="btn btn-primary">
                    <i class="fas fa-check-double"></i>
                    Tandai Semua Dibaca
                </button>
            </form>

            <a href="{{ route('notifications.admin.create') }}"
               class="btn btn-success">
                <i class="fas fa-paper-plane"></i>
                Kirim Notifikasi
            </a>

        </div>

    </div>

    <div class="card">

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">

                <table class="table table-striped table-md">

                    <thead>
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Judul</th>
                            <th>Pesan</th>
                            <th>Penerima</th>
                            <th class="text-center">Tipe</th>
                            <th>Pengirim</th>
                            <th class="text-center">Status</th>
                            <th>Dikirim</th>
                            <th class="text-center action-column">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($notifications as $item)

                            @php
                                $recipients = $item->recipients ?? [];

                                // Support both old (flat array) and new format (mode + count + preview)
                                $displayCount = count($recipients);
                                if (isset($recipients['mode'])) {
                                    $displayCount = $recipients['count'];
                                    $recipients = $recipients['preview'] ?? [];
                                }
                                $totalRecipients = count($recipients);

                                $unreadCount = DB::table('notifications')
                                    ->whereNull('read_at')
                                    ->where('data->notification_log_id', $item->id)
                                    ->count();
                            @endphp

                            <tr>

                                <td class="text-center">
                                    {{ $loop->iteration + ($notifications->firstItem() - 1) }}
                                </td>

                                <td>
                                    <strong>{{ $item->title }}</strong>
                                </td>

                                <td class="message-column">
                                    {{ $item->message }}
                                </td>

                                <td class="recipient-column">

                                    @if($totalRecipients == 0)

                                        <span class="text-muted">{{ $displayCount }} penerima</span>

                                    @elseif($totalRecipients <= 3)

                                        <div class="recipient-wrap">
                                            @foreach($recipients as $r)
                                                <span class="badge badge-primary badge-custom">
                                                    {{ $r }}
                                                </span>
                                            @endforeach
                                        </div>

                                    @else

                                        <div class="recipient-wrap mb-2">

                                            @foreach(array_slice($recipients, 0, 2) as $r)
                                                <span class="badge badge-primary badge-custom">
                                                    {{ $r }}
                                                </span>
                                            @endforeach

                                        </div>

                                        <details>
                                            <summary style="cursor:pointer;">
                                                +{{ $totalRecipients - 2 }} dari {{ $displayCount }} penerima
                                            </summary>

                                            <div class="recipient-wrap mt-2">

                                                @foreach($recipients as $r)
                                                    <span class="badge badge-primary badge-custom">
                                                        {{ $r }}
                                                    </span>
                                                @endforeach

                                            </div>

                                        </details>

                                    @endif

                                </td>

                                <td class="text-center">
                                    <span class="badge badge-{{ $item->type }}">
                                        {{ ucfirst($item->type) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->sender->name ?? '-' }}
                                </td>

                                <td class="text-center">

                                    @if($unreadCount > 0)
                                        <span class="badge badge-warning">
                                            {{ $unreadCount }} Belum Dibaca
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            Sudah Dibaca
                                        </span>
                                    @endif

                                </td>

                                <td style="white-space: nowrap;">
                                    {{ $item->created_at->format('d M Y') }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $item->created_at->format('H:i') }}
                                    </small>
                                </td>

                                <td class="text-center action-column">

                                    <form action="{{ route('notifications.admin.history.destroy', $item->id) }}"
                                          method="POST"
                                          class="d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm"
                                                onclick="return confirm('Hapus notifikasi ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fas fa-bell-slash fa-2x mb-3 d-block"></i>
                                    Belum ada riwayat notifikasi
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>

        </div>

    </div>

</section>

@endsection
