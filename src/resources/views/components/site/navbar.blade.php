<nav class="site-navbar navbar navbar-expand-md p-0" aria-label="Hoofdnavigatie">
    <div class="collapse navbar-collapse" id="siteNavbar">
        <ul class="navbar-nav gap-md-2 py-3 py-md-0" data-overflow-nav>
            <li class="nav-item" data-overflow-item>
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}">Home</a>
            </li>
            <li class="nav-item" data-overflow-item>
                <a href="{{ route('search') }}" class="nav-link {{ request()->routeIs('search') ? 'active fw-semibold' : '' }}">Zoeken</a>
            </li>
            <li class="nav-item" data-overflow-item>
                <a href="{{ route('map') }}" class="nav-link {{ request()->routeIs('map') ? 'active fw-semibold' : '' }}">Kaart</a>
            </li>
            <li class="nav-item" data-overflow-item>
                <a href="{{ route('info') }}" class="nav-link {{ request()->routeIs('info') ? 'active fw-semibold' : '' }}">Info</a>
            </li>
            <li class="nav-item" data-overflow-item>
                <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active fw-semibold' : '' }}">Contact</a>
            </li>
            @auth
                <li class="nav-item auth-nav-divider" aria-hidden="true" data-overflow-item></li>
                <li class="nav-item" data-overflow-item>
                    <a href="{{ route('account.show') }}" class="nav-link {{ request()->routeIs('account.*') ? 'active fw-semibold' : '' }}">Mijn account</a>
                </li>
                <li class="nav-item" data-overflow-item>
                    <a href="{{ route('manage.organisations.index') }}" class="nav-link {{ request()->routeIs('manage.organisations.*') ? 'active fw-semibold' : '' }}">Mijn organisaties</a>
                </li>
                @if (auth()->user()->isAdmin())
                    <li class="nav-item" data-overflow-item>
                        <a href="{{ route('admin.statistics.index') }}" class="nav-link {{ request()->routeIs('admin.statistics.*') ? 'active fw-semibold' : '' }}">Statistieken</a>
                    </li>
                    <li class="nav-item" data-overflow-item>
                        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active fw-semibold' : '' }}">Instellingen</a>
                    </li>
                @endif
            @endauth
            <li class="nav-item dropdown site-more-nav d-none" data-overflow-more>
                <button
                    type="button"
                    class="nav-link dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >Meer</button>
                <ul class="dropdown-menu dropdown-menu-end" data-overflow-menu></ul>
            </li>
            @auth
                <li class="nav-item site-login-item d-md-none">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">Uitloggen</button>
                    </form>
                </li>
            @endauth
        </ul>
    </div>
</nav>
