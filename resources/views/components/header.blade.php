@auth
<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">

    <!-- Left Side -->
    <ul class="navbar-nav me-auto">
        <li>
            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    <!-- Right Side -->
    <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item">
            <a
                href="#"
                class="nav-link nav-link-lg"
                title="Toggle theme"
                x-data="{ dark: document.documentElement.getAttribute('data-bs-theme') === 'dark' }"
                @click.prevent="
                    dark = !dark;
                    const theme = dark ? 'dark' : 'light';
                    document.documentElement.setAttribute('data-bs-theme', theme);
                    localStorage.setItem('theme', theme);
                "
            >
                <i class="fas" :class="dark ? 'fa-sun' : 'fa-moon'"></i>
            </a>
        </li>

        {{-- NOTIFICATION DROPDOWN --}}
        @php
            $unreadCount = auth()->user()->unreadNotifications->count();
            $notifications = auth()->user()->notifications()->latest()->take(10)->get();

            $notificationMap = [
                'App\\Notifications\\DhsUploadedNotification' => [
                    'title' => 'DHS Diunggah',
                    'icon' => 'fas fa-file-alt text-primary',
                    'url' => '/home',
                ],
                'App\\Notifications\\DhsVerifiedNotification' => [
                    'title' => 'DHS Diverifikasi',
                    'icon' => 'fas fa-check-circle text-success',
                    'url' => '/biodata/edit',
                ],
                'App\\Notifications\\DokumenUploadedNotification' => [
                    'title' => 'Dokumen Diunggah',
                    'icon' => 'fas fa-file-upload text-info',
                    'url' => '/verifikasi-dokumen',
                ],
                'App\\Notifications\\DokumenVerifiedNotification' => [
                    'title' => 'Dokumen Diverifikasi',
                    'icon' => 'fas fa-check-circle text-success',
                    'url' => '/verifikasi-dokumen',
                ],
                'App\\Notifications\\BulkDokumenVerifiedNotification' => [
                    'title' => 'Dokumen Diverifikasi',
                    'icon' => 'fas fa-check-double text-success',
                    'url' => '/pendaftaran-kkn',
                ],
                'App\\Notifications\\GeneralNotification' => [
                    'title' => 'Info',
                    'icon' => 'fas fa-bell text-warning',
                    'url' => '/home',
                ],
            ];
        @endphp
        <li class="dropdown">
            <a href="#" class="nav-link nav-link-lg position-relative" id="notifDropdownToggle">
                <i class="fas fa-bell"></i>
                @if($unreadCount > 0)
                    <span class="notification-badge-dot"></span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-right notification-dropdown-menu">
                <div class="notification-dropdown-header">
                    <span class="fw-bold">Notifikasi</span>
                    @if($unreadCount > 0)
                        <span class="text-primary small">{{ $unreadCount }} baru</span>
                    @endif
                </div>

                <div class="notification-dropdown-list" id="notification-list">
                    @forelse($notifications as $notification)
                        @php
                            $map = $notificationMap[$notification->type] ?? ['title' => 'Notifikasi', 'icon' => 'fas fa-bell text-secondary', 'url' => '/home'];
                            $notifTitle = $notification->data['title'] ?? $map['title'];
                            $notifMessage = $notification->data['message'] ?? '';
                            $redirectUrl = $notification->data['url'] ?? $notification->data['action_url'] ?? $map['url'];
                        @endphp
                        <div class="notification-dropdown-item {{ $notification->read_at ? '' : 'unread' }}"
                             style="cursor:pointer"
                             data-url="{{ $redirectUrl }}"
                             data-id="{{ $notification->id }}"
                             onclick="markNotifRead(event, this)">
                            <div class="notification-dropdown-icon">
                                <i class="{{ $map['icon'] }}"></i>
                            </div>
                            <div class="notification-dropdown-content">
                                <div class="notification-dropdown-title">{{ $notifTitle }}</div>
                                <div class="notification-dropdown-desc">{{ Str::limit($notifMessage, 60) }}</div>
                                <div class="notification-dropdown-time">{{ $notification->created_at->diffForHumans() }}</div>
                            </div>
                            @if(!$notification->read_at)
                                <span class="notification-unread-dot"></span>
                            @endif
                        </div>
                    @empty
                        <div class="notification-dropdown-empty">
                            <i class="fas fa-bell-slash"></i>
                            <span>Belum ada notifikasi</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </li>

        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img
                    alt="Profile"
                    src="{{ auth()->user()->mahasiswa?->foto
                        ? storage_url(auth()->user()->mahasiswa->foto)
                        : asset('img/avatar/avatar-1.png') }}"
                    class="rounded-circle mr-1"
                    width="35"
                    height="35"
                    style="object-fit: cover;"
                >
                <div class="d-sm-none d-lg-inline-block">
                    Hi, {{ auth()->user()->name }}
                </div>
            </a>

            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title fw-bold">
                    Welcome, {{ auth()->user()->name }}
                </div>

                <a class="dropdown-item has-icon edit-profile" href="{{ route('profile.edit') }}">
                    <i class="fa fa-user"></i> Edit Profile
                </a>

                <div class="dropdown-divider"></div>

                <a href="{{ route('logout') }}" class="dropdown-item has-icon text-danger"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('notifDropdownToggle');
    const menu = toggle?.nextElementSibling;
    if (!toggle || !menu) return;

    const bsDropdown = bootstrap.Dropdown.getOrCreateInstance(menu.previousElementSibling.parentElement.querySelector('.dropdown'));

    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (menu.classList.contains('show')) {
            bsDropdown.hide();
        } else {
            bsDropdown.show();
        }
    });

    document.addEventListener('click', function(e) {
        if (!menu.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
            bsDropdown.hide();
        }
    });
});

function markNotifRead(e, el) {
    e.stopPropagation();
    const id = el.dataset.id;
    const url = el.dataset.url || '/home';

    fetch('/notifications/' + id + '/mark-as-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(() => {
        window.location.href = url;
    }).catch(() => {
        window.location.href = url;
    });
}
</script>
@endauth
