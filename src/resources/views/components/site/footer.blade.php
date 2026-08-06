<footer class="mt-auto">
    <a href="{{ route('home') }}" class="site-footer-main d-block py-4 text-center text-decoration-none text-white">
        <p class="h5 mb-1">Jongerenkaart</p>
        <p class="mb-0 small">&copy; {{ date('Y') }} De Jongerenkaart - UHasselt</p>
    </a>

    <div class="site-footer-links bg-white">
        <div class="container d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3 py-3 small">
            <a href="{{ route('info') }}" class="link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-75-hover">Info</a>
            <a href="{{ route('privacy') }}#privacy" class="link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-75-hover">Privacy</a>
            <a href="{{ route('privacy') }}#cookies" class="link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-75-hover">Cookies</a>
            <a href="{{ route('privacy') }}#toegankelijkheid" class="link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-75-hover">Toegankelijkheid</a>
            <a href="{{ route('faq') }}" class="link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-75-hover">FAQ</a>
            @guest
                <a href="{{ route('login') }}" class="link-secondary link-offset-2 link-underline-opacity-0 link-underline-opacity-75-hover">Inloggen</a>
            @endguest
        </div>
    </div>
</footer>
