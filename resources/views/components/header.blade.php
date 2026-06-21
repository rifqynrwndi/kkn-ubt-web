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
@endauth
