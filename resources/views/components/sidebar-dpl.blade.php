<li class="menu-header">DPL</li>
<li class="{{ Request::is('home') ? 'active' : '' }}">
    <a class="nav-link" href="{{ url('home') }}">
        <i class="fas fa-fire"></i>
        <span>Dashboard</span>
    </a>
</li>

<li class="menu-header">Kelompok</li>
<li class="{{ Request::is('dpl/kelompok*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('dpl.kelompok.index') }}">
        <i class="fas fa-layer-group"></i>
        <span>Kelompok Binaan</span>
    </a>
</li>

<li class="menu-header">Profil</li>
<li class="{{ Request::is('dpl/profile*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('dpl.profile.edit') }}">
        <i class="fas fa-user-edit"></i>
        <span>Edit Profil</span>
    </a>
</li>
<li class="{{ Request::is('profile/change-password') ? 'active' : '' }}">
    <a class="nav-link" href="{{ url('profile/change-password') }}">
        <i class="fas fa-key"></i>
        <span>Ubah Password</span>
    </a>
</li>
