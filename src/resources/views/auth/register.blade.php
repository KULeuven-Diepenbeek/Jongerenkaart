@extends('layouts.app')

@section('title', 'Account aanmaken')

@section('content')
    <section class="auth-page">
        <div class="auth-card">
            <h1>Account aanmaken</h1>
            <p>Je kunt alleen een account aanvragen met een e-mailadres dat als algemeen e-mailadres of contactpersoon bij een organisatie geregistreerd staat. Een administrator moet het nieuwe account activeren voordat je kunt inloggen.</p>
            <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                @csrf
                <label>
                    <span>Naam</span>
                    <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
                    @error('name')<small class="auth-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>E-mailadres</span>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email')<small class="auth-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>Wachtwoord</span>
                    <input type="password" name="password" autocomplete="new-password" required>
                    @error('password')<small class="auth-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>Herhaal wachtwoord</span>
                    <input type="password" name="password_confirmation" autocomplete="new-password" required>
                </label>
                <button type="submit" class="btn btn-primary">Account aanvragen</button>
            </form>
            <a href="{{ route('login') }}" class="auth-secondary-link">Ik heb al een account</a>
        </div>
    </section>
@endsection
