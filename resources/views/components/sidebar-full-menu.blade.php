@auth
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">

        {{-- BRAND --}}
        <div class="sidebar-brand">
            <a href="{{ route('home') }}">
                KKN UBT
            </a>
        </div>

        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ route('home') }}">
                UBT
            </a>
        </div>

        <ul class="sidebar-menu">

            {{-- ========================================================= --}}
            {{-- DASHBOARD --}}
            {{-- ========================================================= --}}
            <li class="menu-header">
                Dashboard
            </li>

            <li class="{{ Request::is('home') ? 'active' : '' }}">
                <a class="nav-link"
                   href="{{ route('home') }}">

                    <i class="fas fa-fire"></i>
                    <span>Dashboard</span>

                </a>
            </li>


            {{-- ========================================================= --}}
            {{-- MAHASISWA --}}
            {{-- ========================================================= --}}
            @role('mahasiswa')

                <li class="menu-header">
                    Program KKN
                </li>

                <li class="{{ Request::is('program-kkn*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('pendaftaran-kkn.index') }}">

                        <i class="fas fa-layer-group"></i>
                        <span>Program KKN</span>

                    </a>
                </li>

                <li class="{{ Request::is('war*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('war.index') }}">

                        <i class="fas fa-fist-raised"></i>
                        <span>Plotting KKN</span>

                    </a>
                </li>
                {{-- @role('mahasiswa')
                @if(auth()->user()->mahasiswa && auth()->user()->mahasiswa->is_biodata_complete)
                    <li class="{{ Request::is('dokumen-pendaftaran*') ? 'active' : '' }}">
                        <a class="nav-link"
                           href="{{ route('dokumen-pendaftaran.index') }}">

                        <i class="fas fa-file-upload"></i>
                        <span>Dokumen Pendaftaran</span>

                    </a>
                </li>
                @endif
                @endrole --}}

                {{-- nanti tampil kalau sudah masuk kelompok --}}
                {{--
                <li class="{{ Request::is('kelompok-saya*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="#">

                        <i class="fas fa-users"></i>
                        <span>Kelompok Saya</span>

                    </a>
                </li>

                <li class="{{ Request::is('logbook*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="#">

                        <i class="fas fa-book"></i>
                        <span>Logbook</span>

                    </a>
                </li>
                --}}

            @endrole


            {{-- ========================================================= --}}
            {{-- SUPERADMIN --}}
            {{-- ========================================================= --}}
            @role('superadmin')

                {{-- USER MANAGEMENT --}}
                <li class="menu-header">
                    User Management
                </li>

                <li class="{{ Request::is('hakakses*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('hakakses.index') }}">

                        <i class="fas fa-user-shield"></i>
                        <span>Role Access</span>

                    </a>
                </li>

                <li class="{{ Request::is('mahasiswa*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('mahasiswa.index') }}">

                        <i class="fas fa-users"></i>
                        <span>Data Mahasiswa</span>

                    </a>
                </li>

                <li class="{{ Request::is('import-mahasiswa*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('import-mahasiswa.index') }}">

                        <i class="fas fa-upload"></i>
                        <span>Import Mahasiswa</span>

                    </a>
                </li>

                <li class="{{ Request::is('pembimbing-lapangan*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('pembimbing-lapangan.index') }}">

                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Dosen Pembimbing</span>

                    </a>
                </li>


                {{-- AKADEMIK --}}
                <li class="menu-header">
                    Akademik
                </li>

                <li class="{{ Request::is('gelombang*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('gelombang.index') }}">

                        <i class="fas fa-layer-group"></i>
                        <span>Gelombang KKN</span>

                    </a>
                </li>

                <li class="{{ Request::is('fakultas-prodi*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('fakultas-prodi.index') }}">

                        <i class="fas fa-university"></i>
                        <span>Fakultas & Prodi</span>

                    </a>
                </li>

                <li class="{{ Request::is('verifikasi-dokumen*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('verifikasi-dokumen.index') }}">

                        <i class="fas fa-file"></i>
                        <span>Verifikasi Dokumen</span>

                    </a>
                </li>


                {{-- PENEMPATAN --}}
                <li class="menu-header">
                    Penempatan
                </li>

                <li class="{{ Request::is('desa*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('desa.index') }}">

                        <i class="fas fa-map-marker-alt"></i>
                        <span>Data Desa</span>

                    </a>
                </li>

                <li class="{{ Request::is('kelompok-kkn*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('kelompok-kkn.index') }}">

                        <i class="fas fa-users-cog"></i>
                        <span>Kelompok KKN</span>

                    </a>
                </li>

                <li class="{{ Request::is('war*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('admin.war.index') }}">

                        <i class="fas fa-flag"></i>
                        <span>Plotting Kelompok</span>

                    </a>
                </li>

                {{-- MONITORING --}}
                <li class="menu-header">
                    Monitoring
                </li>

                <li class="{{ Request::is('notifications*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('notifications.index') }}">

                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>

                        <livewire:notification-badge />

                    </a>
                </li>

                <li class="{{ Request::is('activity-logs*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('activity-logs.index') }}">

                        <i class="fas fa-history"></i>
                        <span>Activity Logs</span>

                    </a>
                </li>


                {{-- SYSTEM --}}
                <li class="menu-header">
                    System
                </li>

                <li class="{{ Request::is('settings*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('settings.index') }}">

                        <i class="fas fa-cog"></i>
                        <span>Settings</span>

                    </a>
                </li>

            @endrole


            {{-- ========================================================= --}}
            {{-- GLOBAL --}}
            {{-- ========================================================= --}}
            <li class="menu-header">
                Account
            </li>

            @role('mahasiswa')

                <li class="{{ Request::is('notifications*') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('notifications.index') }}">

                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>

                        <livewire:notification-badge />

                    </a>
            @endrole

            @role('superadmin')

                <li class="{{ Request::is('profile/edit') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('profile.edit') }}">

                        <i class="far fa-user"></i>
                        <span>Profile</span>

                    </a>
                </li>

            @else

                <li class="{{ Request::is('biodata/edit') ? 'active' : '' }}">
                    <a class="nav-link"
                       href="{{ route('biodata.edit') }}">

                        <i class="far fa-user"></i>
                        <span>Biodata</span>

                    </a>
                </li>

            @endrole

            <li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
                <a class="nav-link"
                   href="{{ route('profile.change-password') }}">

                    <i class="fas fa-key"></i>
                    <span>Change Password</span>

                </a>
            </li>

        </ul>

    </aside>
</div>
@endauth
