@props([
    'slug',
    'contentPage' => null,
])

@auth
    @if (auth()->user()?->isAdmin())
        <div class="admin-page-edit-bar">
            <span>{{ $contentPage ? 'Aangepaste pagina actief' : 'Standaardpagina uit code' }}</span>
            <a href="{{ route('admin.pages.edit', ['slug' => $slug]) }}" class="btn btn-outline-primary btn-sm">Bewerk pagina</a>
        </div>
    @endif
@endauth
