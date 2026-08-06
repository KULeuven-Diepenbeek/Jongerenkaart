@extends('layouts.app')

@section('title', 'Mijn organisaties')

@section('content')
    <section class="manage-page">
        <div class="manage-heading">
            <div>
                <p class="eyebrow">Beheer</p>
                <h1>Mijn organisaties</h1>
            </div>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('manage.organisations.create') }}" class="btn btn-primary">Organisatie toevoegen</a>
            @endif
        </div>

        <form method="GET" action="{{ route('manage.organisations.index') }}" class="manage-search-form">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" name="q" value="{{ $query }}" placeholder="Zoek op organisatienaam" autocomplete="off">
            <button type="submit" class="btn btn-primary btn-sm">Zoeken</button>
        </form>

        <div class="manage-list">
            @forelse ($organisaties as $organisatie)
                <article class="manage-list-row">
                    <div>
                        <h2>{{ $organisatie->publiekeNaam }}</h2>
                        <p>{{ implode(' - ', array_filter([$organisatie->categorie, $organisatie->formatAdres($organisatie->hoofdAdres())])) }}</p>
                    </div>
                    <div class="manage-list-actions">
                        <a href="{{ route('manage.organisations.edit', ['organisatie' => (string) $organisatie->getKey()]) }}" class="btn btn-outline-primary btn-sm">Bewerken</a>
                        @if (auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('manage.organisations.destroy', ['organisatie' => (string) $organisatie->getKey()]) }}" onsubmit="return confirm('Weet je zeker dat je deze organisatie definitief wilt verwijderen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    Verwijder
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="detail-empty">Geen organisaties gevonden.</p>
            @endforelse
        </div>
    </section>
@endsection
