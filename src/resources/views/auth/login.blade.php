@extends('layouts.app')

@section('title', 'Inloggen')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <h1>Inloggen</h1>
            @if (session('status'))
                <p class="auth-success">{{ session('status') }}</p>
            @endif
            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf
                <label>
                    <span>E-mailadres</span>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email')<small class="auth-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>Wachtwoord</span>
                    <input type="password" name="password" autocomplete="current-password" required>
                    @error('password')<small class="auth-error">{{ $message }}</small>@enderror
                </label>
                <button type="submit" class="btn btn-primary">Inloggen</button>
            </form>
            <p>Om veiligheidsredenen word je uiterlijk één uur na het inloggen automatisch uitgelogd.</p>
            <a href="{{ route('register.create') }}" class="auth-secondary-link">Nog geen account? Account aanmaken</a>
        </div>
    </section>
@endsection
