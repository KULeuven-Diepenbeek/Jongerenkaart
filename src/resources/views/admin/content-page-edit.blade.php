@extends('layouts.app')

@section('title', $label.' bewerken')

@section('content')
    <section class="manage-page">
        <div class="manage-heading">
            <div>
                <p class="eyebrow">Admin</p>
                <h1>{{ $label }} bewerken</h1>
            </div>
            <a href="{{ route($slug) }}" class="btn btn-outline-primary btn-sm">Terug naar pagina</a>
        </div>

        @if ($errors->any())
            <div class="manage-error">
                <strong>Niet alles kon worden opgeslagen.</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="settings-help">
            <h2>Pagina inhoud</h2>
            <p>Vul HTML in om de standaard Blade-inhoud van deze pagina te overschrijven. Laat de inhoud leeg en gebruik reset om terug te vallen op de originele pagina uit de code.</p>
        </div>

        <form method="POST" action="{{ route('admin.pages.update', ['slug' => $slug]) }}" class="content-editor-form">
            @csrf
            @method('PUT')
            <label class="manage-field span-2">
                <span>HTML inhoud</span>
                <textarea name="html" rows="22" spellcheck="false">{{ old('html', $editorHtml) }}</textarea>
            </label>
            <div class="manage-form-actions">
                <button type="submit" class="btn btn-primary">Pagina opslaan</button>
                <a href="{{ route($slug) }}" class="btn btn-outline-primary">Annuleren</a>
            </div>
        </form>

        @if ($contentPage)
            <form method="POST" action="{{ route('admin.pages.reset', ['slug' => $slug]) }}" class="content-reset-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Standaardinhoud herstellen</button>
            </form>
        @endif
    </section>
@endsection
