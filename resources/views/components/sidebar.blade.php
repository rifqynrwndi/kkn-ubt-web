@auth
@php
    $isMahasiswa = auth()->user()->hasRole('mahasiswa');
    $isPembimbing = auth()->user()->hasRole('pembimbing');
    $biodataIncomplete = $isMahasiswa && !auth()->user()->mahasiswa?->is_biodata_complete;
@endphp
<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ url('home') }}">
                <img src="{{ asset('images/logo-ubt.png') }}"
                     alt="UBT"
                     style="height:32px;margin-right:8px;vertical-align:middle;"
                     onerror="this.style.display='none'">
                KKN UBT
            </a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ url('home') }}">
                <img src="{{ asset('images/logo-ubt.png') }}"
                     alt="UBT"
                     style="height:28px;"
                     onerror="this.style.display='none'">
            </a>
        </div>
        <ul class="sidebar-menu">
            @if($isPembimbing)

                @include('components.sidebar-dpl')

            @elseif($isMahasiswa)

                <li class="menu-header">Dashboard</li>
                <li class="{{ Request::is('home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('home') }}">
                        <i class="fas fa-fire"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="{{ Request::is('notifications*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('notifications.index') }}">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                        <livewire:notification-badge />
                    </a>
                </li>

                @if($biodataIncomplete)

                    <li class="menu-header">Biodata</li>

                    <li class="{{ Request::is('biodata*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('biodata.edit') }}">
                            <i class="fas fa-id-card"></i>
                            <span>Lengkapi Biodata</span>
                        </a>
                    </li>

                    <li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('profile/change-password') }}">
                            <i class="fas fa-key"></i>
                            <span>Change Password</span>
                        </a>
                    </li>

                @else

                    @include('components.sidebar-full-menu')

                @endif

            @else

                @include('components.sidebar-full-menu')

            @endif
        </ul>
    </aside>
</div>
@endauth

@if(auth()->user()->hasRole('mahasiswa') && !auth()->user()->mahasiswa?->is_biodata_complete)
<div class="alert alert-warning">
    Biodata Anda belum lengkap.
    <a href="{{ route('biodata.edit') }}" class="alert-link">
        Klik di sini untuk melengkapi biodata.
    </a>
</div>
@endif
