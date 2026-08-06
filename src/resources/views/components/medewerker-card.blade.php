@props(['medewerker'])

<div class="contact-card">
    <div class="icon-wrapper">
        <i class="fa-solid fa-location-dot" style="font-size: 30px; color:var(--jk-primary-dark)"></i>
    </div>
    <div class="contact-content">
        <h3>{{ $medewerker->regio }}</h3>
        <p>{{ $medewerker->naam }}</p>
        <a href="mailto:{{ $medewerker->email }}" class="contact-email">
            <i class="fa fa-envelope"></i>
            {{ $medewerker->email }}
        </a>
    </div>
</div>
