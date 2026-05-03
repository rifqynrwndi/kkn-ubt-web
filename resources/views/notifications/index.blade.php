@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Notifications</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-header justify-content-between flex-wrap">
                <h4>All Notifications</h4>

                <div class="d-flex flex-wrap">

                    @if(auth()->user()->hasRole('superadmin'))
                        <a href="{{ route('notifications.create') }}"
                           class="btn btn-success mr-2 mb-2">
                            <i class="fas fa-paper-plane"></i> Send Notification
                        </a>
                    @endif

                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <form action="{{ route('notifications.mark-all-read') }}"
                              method="POST"
                              class="mr-2 mb-2">
                            @csrf
                            <button class="btn btn-primary">
                                <i class="fas fa-check-double"></i>
                                Mark All Read
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('notifications.destroy-all') }}"
                          method="POST"
                          class="mb-2">
                        @csrf
                        @method('DELETE')

                        <button class="btn btn-danger"
                                onclick="return confirm('Delete all notifications?')">
                            <i class="fas fa-trash"></i>
                            Delete All
                        </button>
                    </form>

                </div>
            </div>

            <div class="card-body p-0">

                @forelse($notifications as $notification)

                    @php
                        $data = $notification->data;

                        $iconColor = match($data['type'] ?? 'info') {
                            'success' => 'text-success',
                            'warning' => 'text-warning',
                            'danger'  => 'text-danger',
                            default   => 'text-primary'
                        };

                        $icon = match($data['type'] ?? 'info') {
                            'success' => 'fa-check-circle',
                            'warning' => 'fa-exclamation-triangle',
                            'danger'  => 'fa-times-circle',
                            default   => 'fa-info-circle'
                        };
                    @endphp

                    <div class="border-bottom px-4 py-3 {{ !$notification->read_at ? 'border-left border-primary' : '' }}">
                        <div class="row align-items-center">

                            <div class="col-auto">
                                <i class="fas {{ $icon }} fa-2x {{ $iconColor }}"></i>
                            </div>

                            <div class="col">
                                <div class="d-flex align-items-center flex-wrap mb-1">
                                    <h6 class="mb-0 mr-2">
                                        {{ $data['title'] ?? 'Notification' }}
                                    </h6>

                                    @if(!$notification->read_at)
                                        <span class="badge badge-primary">
                                            New
                                        </span>
                                    @endif
                                </div>

                                <p class="mb-1">
                                    {{ $data['message'] ?? '' }}
                                </p>

                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>

                            <div class="col-auto text-nowrap">

                                @if(isset($data['action_url']) && $data['action_url'])
                                    <form action="{{ route('notifications.mark-as-read', $notification->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">
                                            {{ $data['action_text'] ?? 'View' }}
                                        </button>
                                    </form>

                                @elseif(!$notification->read_at)
                                    <form action="{{ route('notifications.mark-as-read', $notification->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('notifications.destroy', $notification->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this notification?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </div>

                        </div>
                    </div>

                @empty

                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5>No Notifications</h5>
                        <p class="text-muted mb-0">
                            You don't have any notifications yet.
                        </p>
                    </div>

                @endforelse

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
