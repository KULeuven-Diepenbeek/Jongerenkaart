<template id="search-result-card-template">
    <li class="search-result-card" style="--card-color: #6b7280" tabindex="0" role="link">
        <article>
            <div class="search-result-card-header">
                <div>
                    <h2 data-result-name></h2>
                    <div class="search-result-badges">
                        <p class="search-result-category" data-result-category></p>
                        <span class="search-result-distance-badge" data-result-distance hidden></span>
                        <span class="search-result-free-badge" data-result-free-badge hidden>Gratis</span>
                        <span class="search-result-anonymous-badge" data-result-anonymous-badge hidden>Anoniem</span>
                    </div>
                </div>
                <button type="button" class="favorite-button" data-favorite-button aria-pressed="false">
                    <i class="fa-regular fa-star" aria-hidden="true"></i>
                    <span class="visually-hidden">Bewaar organisatie</span>
                </button>
            </div>

            <a class="search-result-address" data-result-address-link target="_blank" rel="noopener noreferrer">
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <span data-result-address></span>
            </a>

            <p class="search-result-description" data-result-description></p>
            <button type="button" class="description-toggle" data-description-toggle hidden>Lees meer</button>

            <div class="search-result-contact" data-result-contact>
                <a data-result-email-link>
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                    <span data-result-email></span>
                </a>
            </div>
        </article>
    </li>
</template>
