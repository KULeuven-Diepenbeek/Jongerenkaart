<header class="site-header sticky-top">
    <div class="container">
        <div class="site-header-inner d-flex align-items-center gap-3 py-3">
            <a href="{{ route('home') }}" class="site-brand d-flex align-items-center text-decoration-none fw-semibold">
                <span class="site-brand-title">Jongerenkaart</span>
            </a>

            <x-site.navbar />

            <div class="site-header-actions ms-auto d-flex align-items-center gap-2">
                <x-site.theme-toggle />
                @auth
                    <form method="POST" action="{{ route('logout') }}" class="d-none d-md-inline-flex">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">Uitloggen</button>
                    </form>
                @endauth
                <button class="navbar-toggler site-menu-toggle d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Navigatie openen">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </div>
</header>
