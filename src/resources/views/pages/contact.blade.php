@extends('layouts.app')

@section('content')

    <div class="title-banner margin-side">
        <h1>Zit je met vragen of problemen?<br><span style="color: var(--jk-primary)">Contacteer ons</span></h1>
        <p>Heb je vragen of opmerkingen over onze werking of website? Vul het formulier in en we
            openen je e-mailprogramma met het bericht ingevuld.
            <br><br>
            Let op: via dit formulier kan je geen contact opnemen met externe zorgverleners.
        </p>
    </div>
    <div class="contact-info-banner">
        <div class="contact-body margin-side">

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fa fa-check-circle"></i>
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fa fa-exclamation-circle"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="contact-form" id="contact-email-form">

                <div class="form-field">
                    <label for="voornaam">Voornaam *</label>
                    <input type="text" name="voornaam" id="voornaam" required>
                </div>

                <div class="form-field">
                    <label for="naam">Naam *</label>
                    <input type="text" name="naam" id="naam" required>
                </div>

                <div class="form-field">
                    <label for="email">E-mailadres *</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="form-field">
                    <label for="woonplaats">Woonplaats</label>
                    <input type="text" name="woonplaats" id="woonplaats">
                </div>

                <div class="form-field">
                    <label for="onderwerp">Onderwerp *</label>
                    <input type="text" name="onderwerp" id="onderwerp" required>
                </div>

                <div class="form-field">
                    <label for="bericht">Bericht *</label>
                    <textarea name="bericht" id="bericht" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fa fa-envelope"></i>
                        Open in e-mailprogramma
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelector('#contact-email-form').addEventListener('submit', (event) => {
            event.preventDefault();

            const form = event.currentTarget;
            const values = new FormData(form);
            const fullName = [values.get('voornaam'), values.get('naam')].filter(Boolean).join(' ');
            const body = [
                `Naam: ${fullName}`,
                `E-mailadres: ${values.get('email')}`,
                values.get('woonplaats') ? `Woonplaats: ${values.get('woonplaats')}` : null,
                '',
                values.get('bericht'),
            ].filter((line) => line !== null).join('\n');
            const parameters = [
                `subject=${encodeURIComponent(`Jongerenkaart: ${values.get('onderwerp')}`)}`,
                `body=${encodeURIComponent(body)}`,
            ].join('&');

            window.location.href = `mailto:${encodeURIComponent(@json($contactEmail))}?${parameters}`;
        });
    </script>
@endpush

