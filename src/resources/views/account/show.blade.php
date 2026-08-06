@extends('layouts.app')

@section('title', 'Mijn account')

@section('content')
    <section class="auth-page">
        <div class="account-panel">
            <div>
                <p class="eyebrow">Mijn account</p>
                <h1>{{ $user->name }}</h1>
                <p>{{ $user->email }}</p>
            </div>

            <div class="account-info-grid">
                <div>
                    <span>Rol</span>
                    <strong>{{ $user->isAdmin() ? 'Admin' : 'Contactpersoon' }}</strong>
                </div>
                <div>
                    <span>Organisaties</span>
                    <strong>{{ $user->isAdmin() ? 'Alle organisaties' : $organisaties->count() }}</strong>
                </div>
            </div>

            @if ($organisaties->isNotEmpty())
                <div>
                    <h2>Gekoppelde organisaties</h2>
                    <ul class="account-organisation-list">
                        @foreach ($organisaties as $organisatie)
                            <li>
                                <a href="{{ route('organisaties.show', ['id' => (string) $organisatie->getKey()]) }}">
                                    {{ $organisatie->publiekeNaam }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="account-password-section">
                <h2>Wachtwoord wijzigen</h2>
                @if (session('status'))
                    <p class="auth-success">{{ session('status') }}</p>
                @endif
                <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                    @csrf
                    @method('PUT')
                    <label>
                        <span>Huidig wachtwoord</span>
                        <input type="password" name="current_password" autocomplete="current-password" required>
                        @error('current_password')<small class="auth-error">{{ $message }}</small>@enderror
                    </label>
                    <label>
                        <span>Nieuw wachtwoord</span>
                        <input type="password" name="password" autocomplete="new-password" required>
                        @error('password')<small class="auth-error">{{ $message }}</small>@enderror
                    </label>
                    <label>
                        <span>Herhaal nieuw wachtwoord</span>
                        <input type="password" name="password_confirmation" autocomplete="new-password" required>
                    </label>
                    <button type="submit" class="btn btn-primary">Wachtwoord wijzigen</button>
                </form>
            </div>
        </div>
    </section>
@endsection
