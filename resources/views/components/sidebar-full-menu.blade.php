@auth
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">

        <div class="sidebar-brand">
            <a href="{{ route('home') }}">KKN UBT</a>
        </div>

        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('home') }}">UBT</a>
        </div>

        <ul class="sidebar-menu">

            {{-- DASHBOARD --}}
            <li class="menu-header">Dashboard</li>

            <li class="{{ Request::is('home') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('home') }}">
                    <i class="fas fa-fire"></i>
                    <span>Dashboard</span>
                </a>
            </li>


            {{-- ADMIN PANEL --}}
            @role('superadmin')
                <li class="menu-header">Admin Panel</li>

                <li class="{{ Request::is('hakakses*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('hakakses.index') }}">
                        <i class="fas fa-user-shield"></i>
                        <span>Role Access</span>
                    </a>
                </li>

                <li class="{{ Request::is('mahasiswa*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('mahasiswa.index') }}">
                        <i class="fas fa-users"></i>
                        <span>Data Mahasiswa</span>
                    </a>
                </li>

                <li class="{{ Request::is('gelombang*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('gelombang.index') }}">
                        <i class="fas fa-layer-group"></i>
                        <span>Data Gelombang</span>
                    </a>
                </li>

                <li class="{{ Request::is('fakultas-prodi*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('fakultas-prodi.index') }}">
                        <i class="fas fa-university"></i>
                        <span>Fakultas & Prodi</span>
                    </a>
                </li>

                <li class="{{ Request::is('verifikasi-dokumen*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('verifikasi-dokumen.index') }}">
                        <i class="fas fa-file"></i>
                        <span>Verifikasi Dokumen</span>
                    </a>
                </li>
            @endrole


            {{-- FEATURES --}}
            <li class="menu-header">Features</li>

            @role('mahasiswa')
            <li class="{{ Request::is('pendaftaran-kkn*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('pendaftaran-kkn.index') }}">
                    <i class="fas fa-file-signature"></i>
                    <span>Pendaftaran</span>
                </a>
            </li>
            @endrole

            <li class="{{ Request::is('notifications*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('notifications.index') }}">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <livewire:notification-badge />
                </a>
            </li>

            <li class="{{ Request::is('file-manager*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('file-manager.index') }}">
                    <i class="fas fa-folder"></i>
                    <span>File Manager</span>
                </a>
            </li>


            {{-- ADMIN TOOLS --}}
            @role('superadmin')
                <li class="menu-header">Admin Tools</li>

                <li class="{{ Request::is('activity-logs*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('activity-logs.index') }}">
                        <i class="fas fa-history"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>

                <li class="{{ Request::is('settings*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('settings.index') }}">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            @endrole


            {{-- PROFILE --}}
            <li class="menu-header">Profile</li>

            @role('superadmin')
                <li class="{{ Request::is('profile/edit') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('profile.edit') }}">
                        <i class="far fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            @else
                <li class="{{ Request::is('biodata/edit') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('biodata.edit') }}">
                        <i class="far fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
            @endrole

            <li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('profile.change-password') }}">
                    <i class="fas fa-key"></i>
                    <span>Change Password</span>
                </a>
            </li>

        </ul>
    </aside>
</div>
@endauth
