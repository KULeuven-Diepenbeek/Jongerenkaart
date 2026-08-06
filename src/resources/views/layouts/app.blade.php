<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Jongerenkaart')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <script>
        (() => {
            let storedTheme = null;
            try {
                storedTheme = localStorage.getItem('jongerenkaart.theme');
            } catch (error) {}
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = storedTheme || systemTheme;
        })();
    </script>
    @stack('head')
    <style>
        :root {
            --jk-primary: #2563eb;
            --jk-primary-rgb: 37, 99, 235;
            --jk-primary-hover: #1d4ed8;
            --jk-primary-dark: #1e40af;
            --jk-primary-soft: #dbeafe;
            --jk-primary-subtle: #eff6ff;
            --jk-primary-contrast: #ffffff;
            --jk-manage: #f59e0b;
            --jk-manage-rgb: 245, 158, 11;
            --jk-manage-dark: #b45309;
            --jk-manage-soft: #fef3c7;
            --jk-manage-subtle: #fffbeb;
            --jk-danger: #dc2626;
            --jk-danger-dark: #991b1b;
            --jk-danger-soft: #fecaca;
            --jk-danger-subtle: #fef2f2;
            --jk-text: #172033;
            --jk-muted: #64748b;
            --jk-border: #d9e2ec;
            --jk-bg: #f8fafc;
            --jk-surface: #ffffff;
            --jk-surface-soft: #f8fafc;
            --jk-input-bg: #ffffff;
            --jk-shadow: rgba(15, 23, 42, .08);
            color-scheme: light;
        }

        html[data-theme="dark"] {
            --jk-primary: #60a5fa;
            --jk-primary-rgb: 96, 165, 250;
            --jk-primary-hover: #93c5fd;
            --jk-primary-dark: #bfdbfe;
            --jk-primary-soft: #1e3a8a;
            --jk-primary-subtle: #10233f;
            --jk-primary-contrast: #0f172a;
            --jk-manage: #fbbf24;
            --jk-manage-rgb: 251, 191, 36;
            --jk-manage-dark: #fde68a;
            --jk-manage-soft: #713f12;
            --jk-manage-subtle: #261804;
            --jk-danger: #f87171;
            --jk-danger-dark: #fecaca;
            --jk-danger-soft: #7f1d1d;
            --jk-danger-subtle: #2f1111;
            --jk-text: #e5edf7;
            --jk-muted: #a8b5c7;
            --jk-border: #334155;
            --jk-bg: #0f172a;
            --jk-surface: #111c2f;
            --jk-surface-soft: #17243a;
            --jk-input-bg: #0b1220;
            --jk-shadow: rgba(0, 0, 0, .35);
            color-scheme: dark;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--jk-bg);
            color: var(--jk-text);
        }

        a {
            color: var(--jk-primary);
        }

        .site-main {
            flex: 1;
        }

        .site-brand-title {
            color: var(--jk-primary);
            font-size: x-large;
        }

        .site-brand {
            white-space: nowrap;
        }

        .site-header {
            border-bottom: 1px solid var(--jk-border);
            background: var(--jk-surface);
        }

        .site-navbar {
            min-width: 0;
        }

        .site-navbar .navbar-collapse {
            width: 100%;
        }

        .site-navbar .navbar-nav {
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
        }

        .site-navbar .nav-link {
            color: var(--jk-muted);
        }

        .site-navbar .nav-link:hover {
            color: var(--jk-primary);
        }

        .site-navbar .nav-link.active {
            color: var(--jk-text);
        }

        .auth-nav-divider {
            width: 1px;
            min-height: 1.4rem;
            background: var(--jk-border);
            margin-inline: .35rem;
        }

        .site-more-nav .dropdown-toggle {
            border: 0;
            background: transparent;
            white-space: nowrap;
        }

        .site-more-nav .dropdown-menu {
            min-width: 12rem;
            padding: .4rem;
            border-color: var(--jk-border);
            border-radius: .5rem;
            background: var(--jk-surface);
            box-shadow: 0 .65rem 1.5rem rgba(15, 23, 42, .13);
        }

        .site-more-nav .dropdown-menu .nav-link {
            display: block;
            width: 100%;
            padding: .5rem .7rem;
            border-radius: .35rem;
            white-space: nowrap;
        }

        .site-more-nav .dropdown-menu .nav-link:hover,
        .site-more-nav .dropdown-menu .nav-link:focus-visible {
            background: var(--jk-primary-subtle);
        }

        .site-more-nav .dropdown-menu .auth-nav-divider {
            width: auto;
            min-height: 1px;
            margin: .35rem .25rem;
        }

        .site-header .btn-outline-primary,
        .site-navbar .btn-outline-primary {
            --bs-btn-color: var(--jk-primary);
            --bs-btn-border-color: var(--jk-primary);
            --bs-btn-hover-bg: var(--jk-primary);
            --bs-btn-hover-border-color: var(--jk-primary);
            --bs-btn-hover-color: var(--jk-primary-contrast);
        }

        .site-header-actions {
            white-space: nowrap;
        }

        .site-menu-toggle {
            --bs-navbar-toggler-icon-bg: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23172033' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            border-color: var(--jk-border);
        }

        .site-menu-toggle:focus {
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .2);
        }

        .theme-toggle {
            position: relative;
            display: inline-grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            width: 3.6rem;
            height: 1.9rem;
            border: 1px solid var(--jk-border);
            border-radius: 999px;
            background: var(--jk-surface-soft);
            color: var(--jk-muted);
            padding: .18rem;
        }

        .theme-toggle:hover {
            color: var(--jk-text);
            border-color: var(--jk-primary);
        }

        .theme-toggle:focus-visible {
            outline: 0;
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .2);
        }

        .theme-toggle::before {
            content: "";
            position: absolute;
            inset: .18rem auto .18rem .18rem;
            width: 1.45rem;
            border-radius: 50%;
            background: var(--jk-primary);
            box-shadow: 0 2px 7px var(--jk-shadow);
            transition: transform .18s ease;
        }

        html[data-theme="dark"] .theme-toggle::before {
            transform: translateX(1.66rem);
        }

        .theme-toggle i {
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
            font-size: .8rem;
            line-height: 1;
        }

        .theme-toggle .theme-toggle-sun {
            color: #f59e0b;
        }

        .theme-toggle .theme-toggle-moon {
            color: #93c5fd;
        }

        html[data-theme="light"] .theme-toggle .theme-toggle-sun,
        html[data-theme="dark"] .theme-toggle .theme-toggle-moon {
            color: var(--jk-primary-contrast);
        }

        html[data-theme="dark"] .site-menu-toggle {
            --bs-navbar-toggler-icon-bg: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23e5edf7' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        html[data-theme="dark"] .bg-white,
        html[data-theme="dark"] .card,
        html[data-theme="dark"] .modal-content,
        html[data-theme="dark"] .dropdown-menu {
            background-color: var(--jk-surface) !important;
            color: var(--jk-text);
        }

        html[data-theme="dark"] input,
        html[data-theme="dark"] select,
        html[data-theme="dark"] textarea,
        html[data-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select {
            background-color: var(--jk-input-bg);
            border-color: var(--jk-border);
            color: var(--jk-text);
        }

        html[data-theme="dark"] input::placeholder,
        html[data-theme="dark"] textarea::placeholder {
            color: var(--jk-muted);
        }

        @media (min-width: 768px) {
            .site-header-inner {
                display: grid !important;
                grid-template-columns: auto minmax(0, 1fr) auto;
            }

            .site-brand {
                justify-self: start;
            }

            .site-navbar {
                justify-self: stretch;
                width: 100%;
            }

            .site-header-actions {
                justify-self: end;
            }
        }

        @media (max-width: 767.98px) {
            .site-header-inner {
                flex-wrap: wrap;
            }

            .site-navbar {
                flex-basis: 100%;
                order: 3;
                flex: 0 0 100%;
            }

            .site-navbar .navbar-collapse {
                border-top: 1px solid var(--jk-border);
                margin-top: .25rem;
            }

            .site-navbar .navbar-nav {
                align-items: stretch;
                flex-wrap: wrap;
                padding-bottom: .25rem;
            }

            .site-navbar .nav-link {
                padding-left: 0;
                padding-right: 0;
            }

            .auth-nav-divider {
                width: 100%;
                min-height: 1px;
                margin: .35rem 0;
            }

            .site-navbar .btn-outline-primary {
                width: 100%;
                padding: .65rem 0;
                border: 0;
                text-align: left;
                color: var(--jk-primary);
                background: transparent;
            }

            .site-navbar .btn-outline-primary:hover {
                color: var(--jk-primary-hover);
                background: transparent;
            }
        }

        .auth-page {
            width: min(560px, calc(100% - 2rem));
            margin: 0 auto;
            padding: clamp(2.5rem, 7vw, 4rem) 0;
        }

        .auth-card,
        .account-panel {
            display: grid;
            gap: 1.1rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: clamp(1.25rem, 4vw, 2rem);
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .auth-card h1,
        .account-panel h1 {
            margin: 0;
            color: var(--jk-text);
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            font-weight: 800;
        }

        .auth-card p,
        .account-panel p {
            color: var(--jk-muted);
            margin: 0;
        }

        .auth-form {
            display: grid;
            gap: .9rem;
        }

        .auth-form label {
            display: grid;
            gap: .35rem;
            color: var(--jk-text);
            font-weight: 800;
        }

        .auth-form input {
            width: 100%;
            border: 1px solid var(--jk-border);
            border-radius: .65rem;
            padding: .75rem .9rem;
            font-weight: 500;
        }

        .auth-form input:focus {
            border-color: var(--jk-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .15);
            outline: 0;
        }

        .auth-check {
            display: flex !important;
            align-items: center;
            gap: .5rem;
            color: var(--jk-muted) !important;
            font-weight: 600 !important;
        }

        .auth-check input {
            width: auto;
        }

        .auth-error {
            color: var(--jk-danger);
            font-weight: 700;
        }

        .auth-success {
            border-radius: .65rem;
            background: #dcfce7;
            color: #166534 !important;
            padding: .75rem .9rem;
            font-weight: 800;
        }

        .auth-secondary-link {
            color: var(--jk-primary);
            font-weight: 800;
            text-decoration: none;
        }

        .auth-secondary-link:hover {
            text-decoration: underline;
            text-underline-offset: .22rem;
        }

        .account-info-grid {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .account-info-grid div {
            border: 1px solid var(--jk-border);
            border-radius: .65rem;
            padding: .85rem;
        }

        .account-info-grid span {
            display: block;
            color: var(--jk-muted);
            font-size: .85rem;
            font-weight: 800;
        }

        .account-info-grid strong {
            color: var(--jk-text);
        }

        .account-organisation-list {
            display: grid;
            gap: .5rem;
            padding: 0;
            margin: .75rem 0 0;
            list-style: none;
        }

        .account-password-section {
            border-top: 1px solid var(--jk-border);
            padding-top: 1.1rem;
        }

        .account-password-section h2 {
            color: var(--jk-text);
            font-size: 1.2rem;
            font-weight: 800;
            margin: 0 0 .75rem;
        }

        .manage-page {
            width: min(1180px, calc(100% - 2rem));
            margin: 0 auto;
            padding: clamp(2rem, 5vw, 3.5rem) 0 3rem;
        }

        .manage-page .eyebrow {
            color: var(--jk-manage-dark);
        }

        .statistics-heading {
            align-items: flex-end;
        }

        .statistics-intro {
            padding: 1rem 0 1.25rem;
            border-top: 1px solid var(--jk-border);
            color: var(--jk-muted);
        }

        .statistics-intro p {
            margin: 0 0 .35rem;
        }

        .statistics-intro strong,
        .statistics-privacy {
            color: var(--jk-text);
        }

        .statistics-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: .75rem;
            margin-bottom: 2rem;
        }

        .statistics-kpi {
            min-width: 0;
            padding: 1rem;
            border: 1px solid var(--jk-border);
            border-top: .25rem solid var(--jk-manage);
            border-radius: .5rem;
            background: var(--jk-surface);
        }

        .statistics-kpi span,
        .statistics-kpi small {
            display: block;
            color: var(--jk-muted);
        }

        .statistics-kpi strong {
            display: block;
            margin: .25rem 0;
            color: var(--jk-text);
            font-size: 1.7rem;
            line-height: 1.1;
        }

        .statistics-section {
            min-width: 0;
            padding: 1.5rem 0;
            border-top: 1px solid var(--jk-border);
        }

        .statistics-section h2 {
            margin: 0 0 .25rem;
            color: var(--jk-text);
            font-size: 1.2rem;
            font-weight: 800;
        }

        .statistics-section > p,
        .statistics-section-heading p {
            margin: 0;
            color: var(--jk-muted);
        }

        .statistics-section-heading {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: end;
        }

        .statistics-period {
            white-space: nowrap;
            font-size: .88rem;
        }

        .statistics-chart {
            display: grid;
            grid-template-columns: repeat(14, minmax(42px, 1fr));
            align-items: end;
            gap: .45rem;
            height: 230px;
            margin-top: 1rem;
            padding-top: 1.5rem;
            overflow-x: auto;
        }

        .statistics-chart-column {
            display: grid;
            grid-template-rows: 1rem minmax(0, 1fr) 1.4rem;
            align-items: end;
            gap: .25rem;
            height: 100%;
            text-align: center;
        }

        .statistics-chart-value,
        .statistics-chart-column small {
            color: var(--jk-muted);
            font-size: .72rem;
            white-space: nowrap;
        }

        .statistics-chart-bar {
            display: block;
            width: min(30px, 75%);
            min-height: 3px;
            margin: 0 auto;
            border-radius: .25rem .25rem 0 0;
            background: var(--jk-manage);
        }

        .statistics-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 2rem;
        }

        .statistics-ranking {
            display: grid;
            gap: .55rem;
            margin: 1rem 0 0;
            padding-left: 1.4rem;
        }

        .statistics-ranking li:not(.statistics-empty) {
            padding-left: .25rem;
        }

        .statistics-ranking li::marker {
            color: var(--jk-muted);
        }

        .statistics-ranking li,
        .statistics-device-row > div,
        .statistics-definition-list > div {
            color: var(--jk-text);
        }

        .statistics-ranking li:not(.statistics-empty),
        .statistics-device-row > div,
        .statistics-definition-list > div {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .statistics-device-list {
            display: grid;
            gap: .9rem;
            margin-top: 1rem;
        }

        .statistics-device-row strong {
            white-space: nowrap;
        }

        .statistics-device-track {
            display: block;
            height: .45rem;
            margin-top: .35rem;
            overflow: hidden;
            border-radius: .25rem;
            background: var(--jk-border);
        }

        .statistics-device-track span {
            display: block;
            height: 100%;
            background: var(--jk-manage);
        }

        .statistics-definition-list {
            display: grid;
            gap: .65rem;
            margin: 1rem 0 0;
        }

        .statistics-definition-list dt {
            font-weight: 500;
        }

        .statistics-definition-list dd {
            margin: 0;
            font-weight: 800;
        }

        .statistics-footnote {
            margin-top: 1rem !important;
            font-size: .88rem;
        }

        @media (max-width: 767.98px) {
            .statistics-heading,
            .statistics-section-heading {
                align-items: stretch;
                flex-direction: column;
            }

            .statistics-heading .btn {
                width: 100%;
            }

            .statistics-columns {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .statistics-period {
                white-space: normal;
            }
        }

        .manage-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .manage-heading h1 {
            margin: 0;
            color: var(--jk-text);
            font-size: clamp(1.9rem, 4vw, 2.8rem);
            font-weight: 800;
        }

        .manage-search-form {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-left: .25rem solid var(--jk-manage);
            border-radius: .75rem;
            padding: .75rem .9rem;
            margin-bottom: 1rem;
        }

        .manage-search-form input {
            flex: 1;
            min-width: 0;
            border: 0;
            outline: 0;
        }

        .manage-list {
            display: grid;
            gap: .75rem;
        }

        .manage-list-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-left: .25rem solid var(--jk-manage-soft);
            border-radius: .75rem;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .manage-list-row h2 {
            margin: 0 0 .2rem;
            color: var(--jk-text);
            font-size: 1.08rem;
            font-weight: 800;
        }

        .manage-list-row p {
            margin: 0;
            color: var(--jk-muted);
        }

        .manage-list-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-shrink: 0;
        }

        .manage-list-actions form {
            margin: 0;
        }

        .manage-form {
            display: grid;
            gap: 1rem;
        }

        .manage-tab-layout .tab-panel {
            padding: 1rem;
        }

        .manage-grid {
            display: grid;
            gap: .85rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .manage-field {
            display: grid;
            gap: .35rem;
            color: var(--jk-text);
            font-size: .9rem;
            font-weight: 800;
        }

        .manage-field input,
        .manage-field select,
        .manage-field textarea,
        .repeat-row input,
        .repeat-row select {
            width: 100%;
            border: 1px solid var(--jk-border);
            border-radius: .55rem;
            background: #fff;
            color: var(--jk-text);
            padding: .65rem .75rem;
            font-weight: 500;
        }

        .manage-field input:focus,
        .manage-field select:focus,
        .manage-field textarea:focus,
        .repeat-row input:focus,
        .repeat-row select:focus {
            border-color: var(--jk-manage);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-manage-rgb), .16);
            outline: 0;
        }

        .span-2 {
            grid-column: 1 / -1;
        }

        .repeat-list,
        .manage-repeat-choice {
            display: grid;
            gap: .75rem;
            margin-bottom: .75rem;
        }

        .manage-repeat-choice + .manage-repeat-choice,
        .tab-panel > h2 {
            margin-top: 1rem;
        }

        .tab-panel h2,
        .manage-repeat-choice h2 {
            color: var(--jk-text);
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: .2rem;
        }

        .repeat-row {
            display: grid;
            gap: .55rem;
            border: 1px solid var(--jk-border);
            border-left: .2rem solid var(--jk-manage-soft);
            border-radius: .65rem;
            background: #fff;
            padding: .75rem;
        }

        .choice-row {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
        }

        .contact-row {
            grid-template-columns: repeat(5, minmax(0, 1fr)) auto;
        }

        .opening-row {
            grid-template-columns: minmax(120px, 1fr) minmax(120px, 1fr) minmax(100px, .8fr) minmax(100px, .8fr) minmax(100px, .8fr) auto;
        }

        .repeat-remove {
            border: 0;
            background: transparent;
            color: var(--jk-danger);
            font-weight: 800;
            white-space: nowrap;
        }

        .repeat-remove:hover {
            color: var(--jk-danger-dark);
            text-decoration: underline;
            text-underline-offset: .2rem;
        }

        .manage-address-map {
            min-height: 320px;
            border: 1px solid var(--jk-manage-soft);
            border-radius: .75rem;
            margin-top: 1rem;
            overflow: hidden;
        }

        .manage-form-actions {
            display: flex;
            gap: .75rem;
            justify-content: flex-end;
        }

        .manage-danger-zone {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-top: 1px solid var(--jk-danger-soft);
            margin-top: 1.5rem;
            padding-top: 1.25rem;
        }

        .manage-danger-zone h2 {
            margin: 0 0 .25rem;
            color: var(--jk-danger-dark);
            font-size: 1.05rem;
        }

        .manage-danger-zone p {
            margin: 0;
            color: var(--jk-muted);
        }

        .manage-error {
            border: 1px solid var(--jk-danger-soft);
            border-radius: .75rem;
            background: var(--jk-danger-subtle);
            color: var(--jk-danger-dark);
            padding: 1rem;
        }

        .settings-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .settings-help {
            border: 1px solid var(--jk-border);
            border-left: .25rem solid var(--jk-manage);
            border-radius: .75rem;
            background: #fff;
            padding: 1.1rem;
            margin-bottom: 1rem;
        }

        .settings-help h2 {
            color: var(--jk-text);
            font-size: 1.2rem;
            font-weight: 800;
            margin: 0 0 .5rem;
        }

        .settings-help p {
            color: var(--jk-muted);
            margin: 0 0 .75rem;
        }

        .settings-help ul {
            display: grid;
            gap: .35rem;
            margin: 0;
            padding-left: 1.15rem;
        }

        .settings-card {
            display: grid;
            align-content: space-between;
            gap: 1.25rem;
            min-height: 220px;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-left: .25rem solid var(--jk-manage);
            border-radius: .75rem;
            padding: 1.2rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .settings-card-strong {
            background: var(--jk-manage-subtle);
            border-color: var(--jk-manage-soft);
            border-left-color: var(--jk-manage-dark);
        }

        .settings-card h2 {
            color: var(--jk-text);
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0 0 .45rem;
        }

        .settings-card p {
            color: var(--jk-muted);
            margin: 0;
        }

        .settings-card form {
            margin: 0;
        }

        .settings-contact-form {
            display: grid;
            gap: .55rem;
        }

        .settings-contact-form label {
            color: var(--jk-text);
            font-weight: 800;
        }

        .settings-contact-form input {
            width: 100%;
            border: 1px solid var(--jk-border);
            border-radius: .55rem;
            background: var(--jk-input-bg);
            color: var(--jk-text);
            padding: .65rem .75rem;
        }

        .settings-contact-form input:focus {
            border-color: var(--jk-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .15);
            outline: 0;
        }

        .settings-switch {
            display: inline-flex;
            align-items: center;
            gap: .65rem;
            width: fit-content;
            color: var(--jk-text);
            font-weight: 800;
            cursor: pointer;
        }

        .settings-switch input {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            opacity: 0;
        }

        .settings-switch-track {
            position: relative;
            width: 2.75rem;
            height: 1.5rem;
            flex: 0 0 auto;
            border: 1px solid var(--jk-border);
            border-radius: 999px;
            background: var(--jk-muted);
            transition: background-color .18s ease, border-color .18s ease;
        }

        .settings-switch-track::after {
            content: '';
            position: absolute;
            top: .18rem;
            left: .2rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .35);
            transition: transform .18s ease;
        }

        .settings-switch input:checked + .settings-switch-track {
            border-color: var(--jk-primary);
            background: var(--jk-primary);
        }

        .settings-switch input:checked + .settings-switch-track::after {
            transform: translateX(1.25rem);
        }

        .settings-switch input:focus-visible + .settings-switch-track {
            outline: 3px solid rgba(var(--jk-primary-rgb), .35);
            outline-offset: 2px;
        }

        .settings-status {
            border: 1px solid var(--jk-manage-soft);
            border-radius: .75rem;
            background: var(--jk-manage-subtle);
            color: var(--jk-text);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .settings-status strong {
            display: block;
            margin-bottom: .5rem;
            color: var(--jk-manage-dark);
        }

        .settings-status ul {
            display: grid;
            gap: .25rem;
            margin: 0;
            padding-left: 1.1rem;
        }

        .settings-status-error {
            border-color: var(--jk-danger-soft);
            background: var(--jk-danger-subtle);
        }

        .settings-status-error strong {
            color: var(--jk-danger-dark);
        }

        .settings-section {
            display: grid;
            gap: 1rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            margin-top: 1.25rem;
            padding: 1.1rem;
        }

        .settings-section-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }

        .settings-section-heading h2 {
            color: var(--jk-text);
            font-size: 1.25rem;
            font-weight: 850;
            margin: 0 0 .25rem;
        }

        .settings-section-heading p {
            color: var(--jk-muted);
            margin: 0;
        }

        .settings-table,
        .settings-user-list,
        .settings-page-list {
            display: grid;
            gap: .75rem;
        }

        .settings-table-row,
        .settings-user-row,
        .settings-page-row {
            display: grid;
            align-items: center;
            gap: .75rem;
            border: 1px solid var(--jk-border);
            border-radius: .65rem;
            padding: .75rem;
        }

        .settings-table-row {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .settings-page-row {
            grid-template-columns: minmax(0, 1fr) auto;
        }

        .settings-user-row {
            grid-template-columns: minmax(180px, 1fr) auto minmax(280px, 1.4fr) auto;
        }

        .settings-inline-form,
        .settings-create-form,
        .settings-password-form,
        .settings-search-form {
            display: grid;
            gap: .6rem;
            margin: 0;
        }

        .settings-inline-form {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .settings-create-form {
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            align-items: end;
            border-top: 1px solid var(--jk-border);
            padding-top: 1rem;
        }

        .settings-admin-create {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .settings-password-form {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
        }

        .settings-search-form {
            grid-template-columns: minmax(180px, 1fr) auto;
        }

        .settings-inline-form input,
        .settings-create-form input,
        .settings-password-form input,
        .settings-search-form input {
            width: 100%;
            border: 1px solid var(--jk-border);
            border-radius: .55rem;
            padding: .6rem .75rem;
        }

        .settings-row-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .settings-row-actions form,
        .settings-user-row form {
            margin: 0;
        }

        .settings-user-row strong,
        .settings-user-row span,
        .settings-page-row strong,
        .settings-page-row span {
            display: block;
        }

        .settings-user-row strong,
        .settings-page-row strong {
            color: var(--jk-text);
        }

        .settings-user-row span,
        .settings-page-row span {
            color: var(--jk-muted);
            overflow-wrap: anywhere;
        }

        .admin-page-edit-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .75rem;
            max-width: 1180px;
            margin: 1rem auto;
            padding: 0 1rem;
        }

        .admin-page-edit-bar span {
            color: var(--jk-muted);
            font-size: .9rem;
            font-weight: 700;
        }

        .editable-content-page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 1rem 2rem;
        }

        .content-editor-form textarea {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            min-height: 520px;
        }

        .content-reset-form {
            border-top: 1px solid var(--jk-border);
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .settings-role-badge {
            border-radius: 999px;
            background: var(--jk-manage-subtle);
            color: var(--jk-manage-dark) !important;
            font-size: .8rem;
            font-weight: 800;
            padding: .35rem .65rem;
            text-align: center;
            white-space: nowrap;
        }

        .settings-self-note {
            color: var(--jk-muted);
            font-size: .85rem;
            white-space: nowrap;
        }

        .settings-progress-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: grid;
            place-items: center;
            background: rgba(15, 23, 42, .4);
            padding: 1rem;
        }

        .settings-progress-modal[hidden] {
            display: none;
        }

        .settings-progress-modal > div {
            width: min(420px, 100%);
            border-radius: .75rem;
            background: #fff;
            padding: 1.25rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        }

        .settings-progress-modal strong {
            display: block;
            color: var(--jk-text);
            font-size: 1.1rem;
            margin-bottom: .25rem;
        }

        .settings-progress-modal p {
            color: var(--jk-muted);
            margin: 0 0 1rem;
        }

        .settings-progress-bar {
            height: .55rem;
            overflow: hidden;
            border-radius: 999px;
            background: var(--jk-primary-subtle);
            margin-bottom: 1rem;
        }

        .settings-progress-bar span {
            display: block;
            width: 45%;
            height: 100%;
            border-radius: inherit;
            background: var(--jk-primary);
            animation: settings-progress 1.1s ease-in-out infinite alternate;
        }

        @keyframes settings-progress {
            from { transform: translateX(-40%); }
            to { transform: translateX(160%); }
        }

        @media (max-width: 767.98px) {
            .manage-heading,
            .manage-list-row,
            .manage-form-actions,
            .manage-danger-zone {
                align-items: stretch;
                flex-direction: column;
            }

            .manage-list-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .manage-list-actions .btn {
                width: 100%;
            }

            .choice-row,
            .contact-row,
            .opening-row {
                grid-template-columns: minmax(0, 1fr);
            }

            .settings-section-heading,
            .settings-row-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .settings-table-row,
            .settings-page-row,
            .settings-user-row,
            .settings-inline-form,
            .settings-password-form,
            .settings-search-form {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        .site-footer-main {
            background: var(--jk-primary-dark);
        }

        .site-footer-links {
            border-top: 1px solid var(--jk-border);
        }

        .title-banner,
        .info_privacy-banner {
            max-width: 960px;
            margin: 0 auto;
            padding: clamp(2.5rem, 8vw, 5rem) 1rem;
            text-align: center;
        }

        .title-banner h1,
        .info_privacy-banner h1 {
            font-weight: 700;
            line-height: 1.08;
            font-size: clamp(2rem, 6vw, 4rem);
            margin-bottom: 1rem;
        }

        .title-banner p,
        .content-header p,
        .contact-header p,
        .recap-card p {
            color: var(--jk-muted);
            font-size: 1.05rem;
        }

        .margin-side,
        .padding-side {
            width: min(1120px, calc(100% - 2rem));
            margin-inline: auto;
        }

        .padding-side {
            padding-block: 3rem;
        }

        .content,
        .contact-grid,
        .grid,
        .shortcuts {
            display: grid;
            gap: 1rem;
        }

        .content {
            grid-template-columns: minmax(0, 1fr);
            align-items: start;
        }

        @media (min-width: 768px) {
            .content {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 2rem;
            }

            .contact-grid,
            .grid,
            .shortcuts {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 992px) {
            .contact-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .card-data,
        .contact-card,
        .recap-card,
        .small-info-card,
        .shortcuts a {
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .small-info-card h3,
        .card-data h3,
        .contact-card h3 {
            font-size: 1rem;
            font-weight: 700;
        }

        .info-intro-section {
            background: var(--jk-primary-subtle);
        }

        .info-university-band {
            background: #172033;
            padding-block: 1.75rem;
        }

        .info-university-logo {
            display: block;
            width: min(17rem, 80vw);
            height: auto;
            margin-inline: auto;
        }

        .info-card-header {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .75rem;
            color: var(--jk-text);
        }

        .info-card-header h3 {
            margin: 0;
        }

        .info-card-header i {
            color: var(--jk-primary);
        }

        .info-source-card {
            background: #fef3c7;
            border-color: #fcd34d;
        }

        .info-source-card h3 {
            color: #92400e;
        }

        .info-partner-card {
            background: var(--jk-primary-soft);
            border-color: color-mix(in srgb, var(--jk-primary) 30%, var(--jk-border));
        }

        .info-partner-card h3 {
            color: var(--jk-primary-dark);
        }

        .contact-btn,
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border: 1px solid var(--jk-primary);
            border-radius: .5rem;
            background: var(--jk-primary);
            color: #fff;
            padding: .75rem 1rem;
            text-decoration: none;
            font-weight: 600;
        }

        .btn-submit:disabled {
            opacity: .65;
            cursor: not-allowed;
        }

        .contact-form,
        .form-field {
            display: grid;
            gap: .75rem;
        }

        .contact-form {
            max-width: 760px;
            margin: 0 auto 3rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: 1rem;
        }

        .form-field input,
        .form-field textarea {
            width: 100%;
            border: 1px solid var(--jk-border);
            border-radius: .5rem;
            padding: .75rem;
        }

        .form-field textarea {
            min-height: 9rem;
        }

        .shortcuts {
            width: min(900px, calc(100% - 2rem));
            margin: 0 auto 2rem;
        }

        .shortcuts a {
            display: flex;
            align-items: center;
            gap: .75rem;
            color: var(--jk-text);
            text-decoration: none;
        }

        .extra-info-btn {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            background: #fff;
            padding: 1rem;
            margin-top: 1rem;
            text-align: left;
            color: var(--jk-text);
        }

        .extra-info-btn p {
            margin: 0;
            font-weight: 600;
        }

        .extra-info {
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: 1rem;
            margin-top: .75rem;
        }

        .main-container > .content {
            grid-template-columns: minmax(0, 1fr);
            gap: .75rem;
        }

        .sidebar-pc {
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            background: #fff;
            padding: 1rem;
        }

        .sidebar-pc h2 {
            margin: 0 0 .75rem;
            color: var(--jk-text);
            font-size: 1.15rem;
            font-weight: 800;
        }

        .sidebar-content {
            display: grid;
            gap: .25rem;
        }

        .sidebar-content a {
            display: block;
            border-radius: .5rem;
            color: var(--jk-muted);
            padding: .45rem .55rem;
            text-decoration: none;
        }

        .sidebar-content a:hover,
        .sidebar-content a:focus-visible {
            background: var(--jk-primary-subtle);
            color: var(--jk-primary-dark);
        }

        .sidebar-content a p {
            margin: 0;
            color: inherit;
        }

        .sidebar-content a br {
            display: none;
        }

        .home-hero {
            background: linear-gradient(180deg, var(--jk-primary-subtle), #f8fafc);
            padding: clamp(2.5rem, 7vw, 5rem) 0;
        }

        .home-hero-inner {
            display: grid;
            gap: 1.5rem;
            align-items: center;
        }

        .home-hero-copy {
            max-width: 680px;
        }

        .eyebrow {
            color: var(--jk-primary);
            font-weight: 800;
            margin-bottom: .5rem;
            text-transform: uppercase;
            font-size: .78rem;
            letter-spacing: .08em;
        }

        .home-hero h1 {
            font-size: clamp(2.4rem, 7vw, 4.8rem);
            line-height: 1;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .home-hero-copy > p:not(.eyebrow) {
            color: var(--jk-muted);
            font-size: 1.08rem;
            max-width: 58ch;
        }

        .home-search-form {
            display: flex;
            gap: .75rem;
            margin-top: 1.25rem;
            max-width: 620px;
        }

        .home-search-form input {
            flex: 1;
            min-width: 0;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            background: #fff;
            padding: .85rem 1rem;
            outline: 0;
        }

        .home-search-form input:focus {
            border-color: var(--jk-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .15);
        }

        .home-search-form .btn-primary,
        .home-emergency-actions .btn-primary {
            --bs-btn-bg: var(--jk-primary);
            --bs-btn-border-color: var(--jk-primary);
            --bs-btn-hover-bg: var(--jk-primary-hover);
            --bs-btn-hover-border-color: var(--jk-primary-hover);
        }

        .home-hero-panel {
            display: grid;
            gap: .55rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-left: .35rem solid #ef4444;
            border-radius: .75rem;
            padding: 1rem;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .08);
        }

        .home-hero-panel strong {
            color: #ef4444;
            font-size: clamp(1.6rem, 3.5vw, 2.2rem);
            line-height: .9;
        }

        .home-hero-panel span {
            color: var(--jk-muted);
            font-size: .95rem;
        }

        .home-hero-panel span:first-child {
            color: var(--jk-text);
            font-weight: 800;
        }

        .home-emergency-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .home-section {
            padding-block: 2rem;
        }

        .home-location-section {
            padding-bottom: .25rem;
        }

        .home-location-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-left: .35rem solid var(--jk-primary);
            border-radius: .75rem;
            color: var(--jk-text);
            padding: 1rem;
            text-decoration: none;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .home-location-card:hover {
            border-color: var(--jk-primary);
            color: var(--jk-text);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
            transform: translateY(-1px);
        }

        .home-location-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            flex: 0 0 3rem;
            border-radius: 999px;
            background: var(--jk-primary-subtle);
            color: var(--jk-primary);
            font-size: 1.25rem;
        }

        .home-location-copy {
            display: grid;
            gap: .2rem;
            flex: 1;
            min-width: 0;
        }

        .home-location-copy strong {
            font-size: 1.1rem;
        }

        .home-location-copy small {
            color: var(--jk-muted);
        }

        .home-location-action {
            color: var(--jk-primary);
            font-weight: 800;
            white-space: nowrap;
        }

        .section-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .section-heading h2,
        .home-ls-panel h2 {
            font-size: 1.35rem;
            font-weight: 800;
            margin: 0;
        }

        .section-subtitle {
            color: var(--jk-muted);
            margin: .25rem 0 0;
        }

        .home-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .home-guides {
            display: grid;
            gap: 1rem;
        }

        .home-guide-card {
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .home-guide-card {
            display: grid;
            gap: .35rem;
            border-left: .35rem solid var(--guide-color, var(--jk-primary));
            color: var(--jk-text);
            padding: 1rem;
            text-decoration: none;
        }

        .home-guide-card:hover {
            border-color: var(--guide-color, var(--jk-primary));
            color: var(--jk-text);
        }

        .home-guide-card span {
            color: var(--guide-color, var(--jk-text));
            font-weight: 800;
        }

        .home-guide-card small,
        .ls-card-meta,
        .ls-empty {
            color: var(--jk-muted);
        }

        .home-guide-card.urgent {
            border-left: .35rem solid #ef4444;
        }

        .home-ls-section {
            padding-block: 1rem 3rem;
        }

        .home-ls-grid {
            display: grid;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .home-ls-panel {
            padding: 1.1rem;
        }

        .home-ls-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: .5rem;
        }

        .ls-clear-recent {
            border: 0;
            background: transparent;
            color: var(--jk-muted);
            padding: .25rem 0;
            font-size: .85rem;
            font-weight: 700;
            text-align: right;
        }

        .ls-clear-recent:hover {
            color: var(--jk-text);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: .2rem;
        }

        .ls-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .15rem;
            padding: .75rem 0;
        }

        .ls-row + .ls-row {
            border-top: 1px solid var(--jk-border);
        }

        .ls-row-link {
            display: grid;
            gap: .15rem;
            min-width: 0;
            color: var(--jk-text);
            text-decoration: none;
        }

        .ls-row-link:hover .ls-card-name {
            color: var(--jk-primary);
            text-decoration: underline;
            text-underline-offset: .2rem;
        }

        .ls-unfavorite {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            flex: 0 0 2rem;
            border: 1px solid #f59e0b;
            border-radius: 999px;
            background: #fffbeb;
            color: #f59e0b;
        }

        .ls-unfavorite:hover {
            background: #f59e0b;
            color: #fff;
        }

        .ls-card-name {
            font-weight: 700;
        }

        @media (min-width: 768px) {
            .home-hero-inner {
                grid-template-columns: minmax(0, 1fr) minmax(280px, 360px);
            }

            .home-guides,
            .home-ls-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .home-ls-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .home-ls-panel + .home-ls-panel {
                border-left: 1px solid var(--jk-border);
            }
        }

        @media (max-width: 575.98px) {
            .home-search-form,
            .section-heading {
                align-items: stretch;
                flex-direction: column;
            }

            .home-location-card {
                align-items: flex-start;
            }

            .home-location-action {
                align-self: center;
            }
        }

        .search-page {
            width: min(960px, calc(100% - 2rem));
            margin: 0 auto;
            padding: clamp(2rem, 6vw, 4rem) 0;
        }

        .search-shell {
            display: grid;
            gap: 1rem;
        }

        .search-heading h1 {
            font-size: clamp(2rem, 5vw, 3.25rem);
            line-height: 1.1;
            font-weight: 700;
            margin-bottom: .5rem;
        }

        .search-heading p,
        .search-status,
        .search-result-category {
            color: var(--jk-muted);
        }

        .search-input-wrap {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: .85rem 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .search-input-wrap:focus-within {
            border-color: var(--jk-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .15);
        }

        .search-input-wrap i {
            color: var(--jk-primary);
        }

        .search-input-wrap input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: var(--jk-text);
        }

        .search-filter-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
        }

        .search-category-list {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            min-width: 0;
        }

        .map-filter-btn {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            min-height: 2.25rem;
            border: 1px solid var(--cat-color);
            border-radius: 999px;
            background: var(--cat-color);
            color: #fff;
            padding: .4rem .75rem;
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .map-filter-btn:hover,
        .map-filter-btn.active {
            background: var(--cat-color);
            border-color: var(--cat-color);
            color: #fff;
        }

        .map-filter-btn.is-filtered-out {
            background: color-mix(in srgb, var(--cat-color) 22%, #fff);
            border-color: color-mix(in srgb, var(--cat-color) 48%, #fff);
            color: color-mix(in srgb, var(--cat-color) 48%, #111827);
        }

        .map-filter-btn.is-filtered-out:hover {
            background: color-mix(in srgb, var(--cat-color) 34%, #fff);
            border-color: color-mix(in srgb, var(--cat-color) 68%, #fff);
            color: color-mix(in srgb, var(--cat-color) 60%, #111827);
        }

        .accordionItem-count {
            color: inherit;
            font-weight: 600;
            opacity: .8;
        }

        .more-filters-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            min-height: 2.25rem;
            flex: 0 0 auto;
            border: 1px solid var(--jk-border);
            border-radius: 999px;
            background: #fff;
            color: var(--jk-text);
            padding: .4rem .75rem;
            font-size: .9rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .search-filter-actions {
            display: flex;
            align-items: center;
            gap: .6rem;
            flex-wrap: wrap;
        }

        .clear-search-filters {
            border: 0;
            background: transparent;
            color: var(--jk-muted);
            padding: .55rem 0;
            font-weight: 800;
            white-space: nowrap;
        }

        .clear-search-filters:hover {
            color: var(--jk-text);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: .22rem;
        }

        .more-filters-toggle:hover,
        .more-filters-toggle[aria-expanded="true"],
        .more-filters-toggle.has-active-filters {
            border-color: var(--jk-primary);
            color: var(--jk-primary);
            background: var(--jk-primary-subtle);
        }

        .more-filters-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.15rem;
            height: 1.15rem;
            border-radius: 999px;
            background: var(--jk-primary);
            color: #fff;
            font-size: .72rem;
            line-height: 1;
        }

        .more-filters-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .75rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .extra-filter-field {
            display: grid;
            gap: .35rem;
            color: var(--jk-text);
            font-size: .85rem;
            font-weight: 700;
        }

        .extra-filter-field select,
        .extra-filter-field input {
            width: 100%;
            border: 1px solid var(--jk-border);
            border-radius: .5rem;
            background: #fff;
            color: var(--jk-text);
            padding: .6rem .75rem;
            font-weight: 500;
        }

        .extra-filter-field select:focus,
        .extra-filter-field input:focus {
            border-color: var(--jk-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .15);
            outline: 0;
        }

        .extra-filter-toggle,
        .map-anonymous-filter {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            min-height: 2.75rem;
            color: var(--jk-text);
            font-weight: 700;
            cursor: pointer;
        }

        .extra-filter-toggle input,
        .map-anonymous-filter input {
            width: 1.1rem;
            height: 1.1rem;
            flex: 0 0 1.1rem;
            margin: 0;
            padding: 0;
            accent-color: var(--jk-primary);
        }

        .map-anonymous-filter {
            padding: .5rem .75rem;
            border: 1px solid var(--jk-border);
            border-radius: .5rem;
            background: var(--jk-surface);
        }

        .search-result-anonymous-badge,
        .map-result-anonymous {
            color: var(--jk-text);
            font-size: .78rem;
            font-weight: 700;
        }

        .search-result-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 2rem;
        }

        .search-result-controls .search-status {
            margin: 0;
        }

        .show-all-results-toggle {
            border: 0;
            background: transparent;
            color: var(--jk-text);
            padding: .25rem 0;
            font-weight: 800;
            white-space: nowrap;
        }

        .show-all-results-toggle:hover {
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: .22rem;
        }

        .map-page {
            width: min(1180px, calc(100% - 2rem));
            margin: 0 auto;
            padding: clamp(2rem, 5vw, 3.5rem) 0 3rem;
        }

        .map-heading {
            margin-bottom: 1.25rem;
        }

        .map-heading h1 {
            color: var(--jk-text);
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            line-height: 1.05;
            margin-bottom: .5rem;
        }

        .map-heading p {
            color: var(--jk-muted);
            max-width: 62ch;
        }

        .map-toolbar {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            margin-bottom: .9rem;
        }

        .map-search-field {
            min-width: min(100%, 280px);
        }

        .map-field {
            display: grid;
            gap: .35rem;
            color: var(--jk-text);
            font-size: .9rem;
            font-weight: 800;
        }

        .map-field select,
        .map-field input {
            width: 100%;
            border: 1px solid var(--jk-border);
            border-radius: .65rem;
            background: #fff;
            color: var(--jk-text);
            padding: .75rem .9rem;
            font-weight: 600;
        }

        .map-field select:focus,
        .map-field input:focus {
            border-color: var(--jk-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .15);
            outline: 0;
        }

        .map-search-input-wrap {
            display: flex;
            align-items: center;
            gap: .65rem;
            border: 1px solid var(--jk-border);
            border-radius: .65rem;
            background: #fff;
            padding: 0 .85rem;
        }

        .map-search-input-wrap:focus-within {
            border-color: var(--jk-primary);
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .15);
        }

        .map-search-input-wrap i {
            color: var(--jk-muted);
        }

        .map-search-input-wrap input {
            border: 0;
            box-shadow: none;
            padding-inline: 0;
        }

        .map-search-input-wrap input:focus {
            border: 0;
            box-shadow: none;
        }

        .map-pin-mode {
            align-self: end;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 3.05rem;
            border: 1px solid var(--jk-border);
            border-radius: .65rem;
            background: #fff;
            color: var(--jk-text);
            padding: .75rem .9rem;
            font-weight: 800;
        }

        .map-pin-mode:hover,
        .map-pin-mode.active {
            border-color: var(--jk-primary);
            background: var(--jk-primary-subtle);
            color: var(--jk-primary);
        }

        .map-page .map-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1rem;
        }

        .map-layout {
            display: grid;
            gap: 1rem;
        }

        .organisaties-map {
            min-height: min(72vh, 680px);
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            overflow: hidden;
            background: #e5e7eb;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .map-results-panel {
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .map-results-heading {
            margin-bottom: .75rem;
        }

        .map-results-heading h2 {
            color: var(--jk-text);
            font-size: 1.15rem;
            font-weight: 800;
            margin: 0;
        }

        .map-results-heading p,
        .map-empty {
            color: var(--jk-muted);
            margin: .25rem 0 0;
        }

        .map-results {
            display: grid;
            gap: .65rem;
            max-height: 560px;
            overflow: auto;
            padding-right: .15rem;
        }

        .map-result-card {
            display: grid;
            gap: .22rem;
            width: 100%;
            border: 1px solid var(--jk-border);
            border-left: .35rem solid var(--card-color);
            border-radius: .65rem;
            background: #fff;
            color: var(--jk-text);
            padding: .75rem;
            text-align: left;
        }

        .map-result-card:hover,
        .map-result-card.active {
            border-color: color-mix(in srgb, var(--card-color) 45%, var(--jk-border));
            background: color-mix(in srgb, var(--card-color) 8%, #fff);
        }

        .map-result-name {
            font-weight: 800;
        }

        .map-result-meta,
        .map-result-address {
            color: var(--jk-muted);
            font-size: .9rem;
        }

        .map-result-address {
            display: inline-flex;
            align-items: flex-start;
            gap: .4rem;
        }

        .category-map-pin-wrap,
        .selected-place-pin-wrap,
        .category-cluster-wrap {
            background: transparent;
            border: 0;
        }

        .category-map-pin {
            display: block;
            border: 3px solid #fff;
            border-radius: 999px;
            background: var(--pin-color);
            box-shadow: 0 4px 12px rgba(15, 23, 42, .35);
        }

        .category-cluster {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #fff;
            border-radius: 999px;
            background: var(--cluster-color);
            color: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .28);
            font-size: .85rem;
            font-weight: 800;
        }

        .selected-place-pin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: var(--jk-text);
            color: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, .3);
        }

        .map-popup {
            display: grid;
            gap: .25rem;
            max-width: 260px;
            overflow-wrap: anywhere;
        }

        .map-popup strong {
            color: var(--jk-text);
        }

        .map-popup span,
        .map-popup small,
        .map-popup p {
            color: var(--jk-muted);
        }

        .map-popup p {
            display: -webkit-box;
            margin: .2rem 0;
            max-height: 5.2rem;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 4;
        }

        .map-popup a {
            color: var(--jk-primary);
            font-weight: 800;
            text-decoration: none;
        }

        .map-popup a:hover {
            text-decoration: underline;
            text-underline-offset: .2rem;
        }

        @media (max-width: 767.98px) {
            .search-filter-row {
                flex-direction: column;
            }

            .more-filters-toggle {
                width: 100%;
            }

            .search-filter-actions,
            .clear-search-filters {
                width: 100%;
            }

            .search-result-controls {
                align-items: flex-start;
                flex-direction: column;
                gap: .35rem;
            }
        }

        @media (min-width: 992px) {
            .map-layout {
                grid-template-columns: minmax(0, 1fr) 360px;
                align-items: start;
            }

            .map-results-panel {
                position: sticky;
                top: 6rem;
            }
        }

        .search-results {
            display: grid;
            gap: 1rem;
            grid-template-columns: minmax(0, 1fr);
            list-style: none;
            padding: 0;
            margin: 0;
        }

        @media (min-width: 900px) {
            .search-results {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: start;
            }
        }

        .search-result-card {
            background: #fff;
            border: 1px solid var(--jk-border);
            border-left: .35rem solid var(--card-color);
            border-radius: .75rem;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            cursor: pointer;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }

        .search-result-card:hover,
        .search-result-card:focus-visible {
            border-color: color-mix(in srgb, var(--card-color) 45%, var(--jk-border));
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
            outline: 0;
        }

        .search-result-card:active {
            transform: translateY(1px);
        }

        .search-result-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .75rem;
        }

        .search-result-card h2 {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0 0 .25rem;
        }

        .search-result-card p {
            margin: .25rem 0;
        }

        .search-result-badges {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .4rem;
        }

        .search-result-category,
        .search-result-distance-badge,
        .search-result-free-badge,
        .search-result-anonymous-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .82rem;
            font-weight: 700;
        }

        .search-result-category {
            background: color-mix(in srgb, var(--card-color) 14%, #fff);
            color: color-mix(in srgb, var(--card-color) 70%, #111827);
        }

        .search-result-free-badge {
            background: #dcfce7;
            color: #166534;
        }

        .search-result-anonymous-badge,
        .badge-anonymous {
            background: #e0f2fe;
            color: #075985;
        }

        .search-result-distance-badge {
            background: #f1f5f9;
            color: var(--jk-text);
        }

        .favorite-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            flex: 0 0 2.25rem;
            border: 1px solid var(--jk-border);
            border-radius: 999px;
            background: #fff;
            color: var(--jk-muted);
        }

        .favorite-button:hover,
        .favorite-button.is-favorite {
            border-color: #f59e0b;
            color: #f59e0b;
            background: #fffbeb;
        }

        .search-result-address,
        .search-result-contact a {
            display: inline-flex;
            align-items: flex-start;
            gap: .5rem;
            border-radius: .4rem;
            color: var(--jk-muted);
            padding: .15rem .25rem;
            margin-left: -.25rem;
            text-decoration: none;
            font-size: .95rem;
        }

        .search-result-address {
            margin-bottom: .75rem;
        }

        .search-result-address:hover,
        .search-result-contact a:hover {
            background: color-mix(in srgb, var(--card-color) 10%, #fff);
            color: var(--jk-text);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: .25rem;
        }

        .search-result-address i,
        .search-result-contact i {
            color: var(--card-color);
            margin-top: .15rem;
        }

        .search-result-description {
            color: var(--jk-text);
            line-height: 1.55;
        }

        .search-result-description.is-collapsed {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .description-toggle {
            border: 0;
            background: transparent;
            color: var(--jk-text);
            padding: .25rem 0 0;
            font-weight: 700;
        }

        .search-result-contact {
            border-top: 1px solid var(--jk-border);
            margin-top: 1rem;
            padding-top: .85rem;
        }

        .search-result-contact a {
            overflow-wrap: anywhere;
        }

        .match {
            color: var(--jk-primary);
            font-weight: 700;
        }

        .detail-container {
            width: min(1120px, calc(100% - 2rem));
            margin: 0 auto 3rem;
        }

        .detail-back {
            margin-bottom: 1rem;
        }

        .detail-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-left: .4rem solid var(--detail-color, var(--jk-primary));
            border-radius: .75rem;
            padding: 1.25rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .detail-header h1 {
            margin: 0 0 .75rem;
            color: var(--jk-text);
            font-size: clamp(1.6rem, 3vw, 2.25rem);
            font-weight: 800;
            line-height: 1.1;
        }

        .detail-badges {
            display: flex;
            flex-wrap: wrap;
            gap: .45rem;
        }

        .detail-badges .badge {
            border-radius: 999px;
            padding: .4rem .65rem;
            font-weight: 700;
        }

        .badge-sub,
        .badge-leeftijd {
            background: #f1f5f9;
            color: var(--jk-text);
        }

        .badge-free {
            background: #dcfce7;
            color: #166534;
        }

        .fav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 2.5rem;
            border: 1px solid var(--jk-border);
            border-radius: 999px;
            background: #fff;
            color: var(--jk-muted);
            transition: color .15s ease, border-color .15s ease, background .15s ease;
        }

        .fav-btn:hover,
        .fav-btn.active {
            border-color: #f59e0b;
            background: #fffbeb;
            color: #f59e0b;
        }

        .detail-quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin: 1rem 0 1.25rem;
        }

        .detail-quick-actions a,
        .contact-list a,
        .persoon-contact a,
        .address-link {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            color: var(--jk-muted);
            text-decoration: none;
            border-radius: .45rem;
        }

        .detail-quick-actions a {
            background: #fff;
            border: 1px solid var(--jk-border);
            padding: .65rem .85rem;
            font-weight: 700;
        }

        .detail-quick-actions a:hover,
        .contact-list a:hover,
        .persoon-contact a:hover,
        .address-link:hover {
            color: var(--jk-text);
            background: color-mix(in srgb, var(--detail-color, var(--jk-primary)) 10%, #fff);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: .25rem;
        }

        .detail-quick-actions i,
        .contact-list i,
        .persoon-contact i,
        .address-link i {
            color: var(--detail-color, var(--jk-primary));
        }

        .tab-layout {
            display: grid;
            gap: 1rem;
        }

        .tab-nav {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            padding-bottom: .2rem;
        }

        .tab-btn {
            flex: 0 0 auto;
            border: 1px solid var(--jk-border);
            border-radius: 999px;
            background: #fff;
            color: var(--jk-muted);
            padding: .65rem .9rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .tab-btn:hover,
        .tab-btn.active {
            border-color: var(--detail-color, var(--jk-primary));
            color: var(--jk-text);
            background: color-mix(in srgb, var(--detail-color, var(--jk-primary)) 11%, #fff);
        }

        .tab-content {
            min-width: 0;
        }

        .tab-panel {
            display: none;
            background: #fff;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            padding: 1.1rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        }

        .tab-panel.active {
            display: block;
        }

        .detail-section + .detail-section {
            border-top: 1px solid var(--jk-border);
            margin-top: 1rem;
            padding-top: 1rem;
        }

        .detail-section h2,
        .tab-panel h2 {
            margin: 0 0 .75rem;
            color: var(--jk-text);
            font-size: 1.1rem;
            font-weight: 800;
        }

        .detail-section p,
        .tab-panel p {
            color: var(--jk-text);
            line-height: 1.6;
        }

        .detail-subsection-title,
        .admin-block h3 {
            color: var(--jk-text);
            font-size: .95rem;
            font-weight: 800;
        }

        .uren-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            overflow: hidden;
            border-radius: .5rem;
        }

        .uren-table th,
        .uren-table td {
            border-bottom: 1px solid var(--jk-border);
            padding: .6rem .5rem;
            text-align: left;
        }

        .uren-table th {
            background: #f8fafc;
            color: var(--jk-muted);
            font-size: .85rem;
        }

        .rubriek-list,
        .contact-list,
        .aanbod-list,
        .admin-block ul {
            display: grid;
            gap: .5rem;
            padding-left: 0;
            margin: 0;
            list-style: none;
        }

        .rubriek-code,
        .primair-badge,
        .adres-label-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: color-mix(in srgb, var(--detail-color, var(--jk-primary)) 12%, #fff);
            color: color-mix(in srgb, var(--detail-color, var(--jk-primary)) 75%, #111827);
            padding: .2rem .5rem;
            font-size: .78rem;
            font-weight: 800;
        }

        .detail-empty {
            margin: 0;
            color: var(--jk-muted);
        }

        .adres-lijst,
        .personen-grid,
        .erkenning-list,
        .admin-grid {
            display: grid;
            gap: .85rem;
        }

        .adres-item,
        .persoon-card,
        .erkenning-item,
        .admin-block {
            border: 1px solid var(--jk-border);
            border-radius: .7rem;
            padding: .9rem;
            background: #fff;
        }

        .adres-hoofdadres {
            border-color: color-mix(in srgb, var(--detail-color, var(--jk-primary)) 42%, var(--jk-border));
        }

        .adres-item-header {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: .5rem;
        }

        .adres-type,
        .adres-meta,
        .persoon-rol,
        .erkenning-door,
        .erkenning-meta {
            color: var(--jk-muted);
        }

        .persoon-name,
        .erkenning-als {
            color: var(--jk-text);
            font-weight: 800;
        }

        .persoon-contact {
            margin-top: .45rem;
            overflow-wrap: anywhere;
        }

        #detail-map {
            min-height: 240px;
            max-height: 320px;
            border: 1px solid var(--jk-border);
            border-radius: .75rem;
            overflow: hidden;
        }

        .extra-dl {
            display: grid;
            gap: .65rem;
            margin: 0;
        }

        .extra-dl dt {
            color: var(--jk-muted);
            font-size: .85rem;
            font-weight: 800;
        }

        .extra-dl dd {
            margin: -.4rem 0 .35rem;
            color: var(--jk-text);
        }

        @media (min-width: 768px) {
            .personen-grid,
            .admin-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 992px) {
            .tab-layout {
                grid-template-columns: 240px minmax(0, 1fr);
                align-items: start;
            }

            .tab-nav {
                position: sticky;
                top: 6rem;
                display: grid;
                gap: .45rem;
                overflow: visible;
            }

            .tab-btn {
                border-radius: .6rem;
                text-align: left;
                white-space: normal;
            }
        }

        .sidebar-pc {
            display: none;
        }

        .sidebar-phone {
            margin-bottom: 1rem;
        }

        @media (min-width: 992px) {
            .main-container {
                display: grid;
                grid-template-columns: 280px minmax(0, 1fr);
                gap: 2rem;
                width: min(1120px, calc(100% - 2rem));
                margin: 0 auto 3rem;
            }

            .sidebar-pc {
                display: block;
                position: sticky;
                top: 6rem;
            }

            .sidebar-phone {
                display: none;
            }
        }

        html[data-theme="dark"] {
            scrollbar-color: var(--jk-border) var(--jk-bg);
        }

        html[data-theme="dark"] .site-footer-main {
            background: #0b1220;
        }

        html[data-theme="dark"] .auth-card,
        html[data-theme="dark"] .account-panel,
        html[data-theme="dark"] .manage-search-form,
        html[data-theme="dark"] .manage-list-row,
        html[data-theme="dark"] .manage-form,
        html[data-theme="dark"] .manage-field input,
        html[data-theme="dark"] .manage-field select,
        html[data-theme="dark"] .manage-field textarea,
        html[data-theme="dark"] .repeat-row,
        html[data-theme="dark"] .settings-help,
        html[data-theme="dark"] .settings-card,
        html[data-theme="dark"] .settings-section,
        html[data-theme="dark"] .settings-table-row,
        html[data-theme="dark"] .settings-user-row,
        html[data-theme="dark"] .settings-page-row,
        html[data-theme="dark"] .settings-progress-modal > div,
        html[data-theme="dark"] .contact-form,
        html[data-theme="dark"] .search-shell,
        html[data-theme="dark"] .search-panel,
        html[data-theme="dark"] .search-result-card,
        html[data-theme="dark"] .result-card,
        html[data-theme="dark"] .organisation-card,
        html[data-theme="dark"] .map-page,
        html[data-theme="dark"] .map-sidebar,
        html[data-theme="dark"] .map-results-panel,
        html[data-theme="dark"] .map-result-card,
        html[data-theme="dark"] .detail-header,
        html[data-theme="dark"] .detail-section,
        html[data-theme="dark"] .detail-quick-actions,
        html[data-theme="dark"] .detail-quick-actions a,
        html[data-theme="dark"] .adres-item,
        html[data-theme="dark"] .persoon-card,
        html[data-theme="dark"] .admin-block,
        html[data-theme="dark"] .erkenning-item,
        html[data-theme="dark"] .tab-layout,
        html[data-theme="dark"] .tab-content,
        html[data-theme="dark"] .tab-panel,
        html[data-theme="dark"] .extra-dl,
        html[data-theme="dark"] .home-hero-panel,
        html[data-theme="dark"] .home-card,
        html[data-theme="dark"] .home-guide-card,
        html[data-theme="dark"] .home-history-section,
        html[data-theme="dark"] .home-ls-grid,
        html[data-theme="dark"] .home-location-card,
        html[data-theme="dark"] .contact-card,
        html[data-theme="dark"] .card-data,
        html[data-theme="dark"] .small-info-card,
        html[data-theme="dark"] .recap-card,
        html[data-theme="dark"] .sidebar-pc,
        html[data-theme="dark"] .sidebar-phone,
        html[data-theme="dark"] .extra-info {
            background: var(--jk-surface) !important;
            border-color: var(--jk-border) !important;
            color: var(--jk-text) !important;
            box-shadow: none;
        }

        html[data-theme="dark"] .home-hero {
            background: linear-gradient(180deg, var(--jk-primary-subtle), var(--jk-bg));
        }

        html[data-theme="dark"] .home-hero-panel {
            border-left-color: #ef4444 !important;
        }

        html[data-theme="dark"] .home-hero-panel strong {
            color: #ef4444 !important;
        }

        html[data-theme="dark"] .padding-side.info-intro-section {
            background: var(--jk-primary-subtle) !important;
        }

        html[data-theme="dark"] .info-source-card,
        html[data-theme="dark"] .small-info-card[style*="#fef3c7"] {
            background: #352807 !important;
            border-color: #785f16 !important;
        }

        html[data-theme="dark"] .info-source-card h3,
        html[data-theme="dark"] .small-info-card[style*="#fef3c7"] h3 {
            color: #fcd34d !important;
        }

        html[data-theme="dark"] .info-partner-card,
        html[data-theme="dark"] .small-info-card[style*="var(--jk-primary-soft)"] {
            background: var(--jk-primary-subtle) !important;
            border-color: #315b92 !important;
        }

        html[data-theme="dark"] .info-partner-card h3,
        html[data-theme="dark"] .small-info-card[style*="var(--jk-primary-soft)"] h3 {
            color: var(--jk-primary-dark) !important;
        }

        html[data-theme="dark"] .info-card-header,
        html[data-theme="dark"] .card-data .card-header {
            background: transparent;
            border-color: var(--jk-border);
            color: var(--jk-text);
        }

        html[data-theme="dark"] .padding-side,
        html[data-theme="dark"] .title-banner,
        html[data-theme="dark"] .info_privacy-banner,
        html[data-theme="dark"] .margin-side,
        html[data-theme="dark"] .content,
        html[data-theme="dark"] .left,
        html[data-theme="dark"] .right {
            background-color: var(--jk-bg) !important;
            color: var(--jk-text);
        }

        html[data-theme="dark"] h1,
        html[data-theme="dark"] h2,
        html[data-theme="dark"] h3,
        html[data-theme="dark"] h4,
        html[data-theme="dark"] h5,
        html[data-theme="dark"] h6,
        html[data-theme="dark"] p,
        html[data-theme="dark"] li,
        html[data-theme="dark"] dt,
        html[data-theme="dark"] dd,
        html[data-theme="dark"] label,
        html[data-theme="dark"] strong,
        html[data-theme="dark"] .tab-btn,
        html[data-theme="dark"] .detail-empty {
            color: var(--jk-text);
        }

        html[data-theme="dark"] .text-muted,
        html[data-theme="dark"] .muted,
        html[data-theme="dark"] .eyebrow,
        html[data-theme="dark"] .account-panel p,
        html[data-theme="dark"] .settings-card p,
        html[data-theme="dark"] .settings-help p,
        html[data-theme="dark"] .settings-section-heading p,
        html[data-theme="dark"] .detail-subsection-title,
        html[data-theme="dark"] .adres-meta,
        html[data-theme="dark"] .erkenning-meta,
        html[data-theme="dark"] .map-result-distance {
            color: var(--jk-muted) !important;
        }

        html[data-theme="dark"] .tab-btn,
        html[data-theme="dark"] .extra-info-btn,
        html[data-theme="dark"] .btn-outline-primary {
            border-color: var(--jk-border);
        }

        html[data-theme="dark"] .extra-info-btn:hover,
        html[data-theme="dark"] .btn-outline-primary:hover {
            background: var(--jk-primary-subtle);
            border-color: var(--jk-primary);
            color: var(--jk-primary-dark);
        }

        html[data-theme="dark"] .tab-btn {
            background: var(--jk-input-bg);
            border-color: #52627a;
            color: var(--jk-muted);
        }

        html[data-theme="dark"] .tab-btn:hover,
        html[data-theme="dark"] .tab-btn.active {
            background: color-mix(in srgb, var(--detail-color, var(--jk-primary)) 22%, var(--jk-surface));
            border-color: var(--detail-color, var(--jk-primary));
            color: var(--jk-text);
        }

        html[data-theme="dark"] .search-result-category {
            background: color-mix(in srgb, var(--card-color) 68%, #0b1220);
            border: 1px solid color-mix(in srgb, var(--card-color) 78%, #ffffff);
            color: #ffffff !important;
        }

        html[data-theme="dark"] .sidebar-content a {
            color: var(--jk-muted);
        }

        html[data-theme="dark"] .sidebar-content a:hover,
        html[data-theme="dark"] .sidebar-content a:focus-visible {
            background: var(--jk-primary-subtle);
            color: var(--jk-primary-dark);
        }

        html[data-theme="dark"] .extra-info-btn {
            background: var(--jk-surface);
            color: var(--jk-text);
        }

        html[data-theme="dark"] .extra-info-btn .arrow {
            color: var(--jk-primary-dark);
        }

        html[data-theme="dark"] .extra-info [style*="color: rgb(51, 50, 50)"] {
            color: var(--jk-text) !important;
        }

        html[data-theme="dark"] table,
        html[data-theme="dark"] th,
        html[data-theme="dark"] td {
            border-color: var(--jk-border) !important;
            color: var(--jk-text);
        }

        html[data-theme="dark"] .leaflet-container,
        html[data-theme="dark"] #detail-map,
        html[data-theme="dark"] #map,
        html[data-theme="dark"] #manage-address-map {
            background: #1f2937;
        }

        html[data-theme="dark"] .leaflet-popup-content-wrapper,
        html[data-theme="dark"] .leaflet-popup-tip {
            background: var(--jk-surface);
            color: var(--jk-text);
        }

        html[data-theme="dark"] .badge-sub,
        html[data-theme="dark"] .badge-leeftijd,
        html[data-theme="dark"] .badge-free,
        html[data-theme="dark"] .badge-anonymous,
        html[data-theme="dark"] .search-result-anonymous-badge,
        html[data-theme="dark"] .primair-badge {
            background: var(--jk-surface-soft);
            color: var(--jk-text);
        }

        html[data-theme="dark"] input:not([type="checkbox"]):not([type="radio"]),
        html[data-theme="dark"] select,
        html[data-theme="dark"] textarea,
        html[data-theme="dark"] .form-control,
        html[data-theme="dark"] .form-select,
        html[data-theme="dark"] .search-input-wrap,
        html[data-theme="dark"] .map-search-input-wrap,
        html[data-theme="dark"] .more-filters-panel,
        html[data-theme="dark"] .more-filters-toggle {
            background-color: var(--jk-input-bg) !important;
            border-color: #52627a !important;
            color: var(--jk-text) !important;
        }

        html[data-theme="dark"] input::placeholder,
        html[data-theme="dark"] textarea::placeholder {
            color: #93a4ba !important;
            opacity: 1;
        }

        html[data-theme="dark"] input:focus,
        html[data-theme="dark"] select:focus,
        html[data-theme="dark"] textarea:focus,
        html[data-theme="dark"] .form-control:focus,
        html[data-theme="dark"] .form-select:focus {
            border-color: var(--jk-primary) !important;
            box-shadow: 0 0 0 .2rem rgba(var(--jk-primary-rgb), .28) !important;
            outline: 0;
        }

        html[data-theme="dark"] .btn-primary {
            --bs-btn-color: #07111f;
            --bs-btn-bg: var(--jk-primary);
            --bs-btn-border-color: var(--jk-primary);
            --bs-btn-hover-color: #07111f;
            --bs-btn-hover-bg: var(--jk-primary-hover);
            --bs-btn-hover-border-color: var(--jk-primary-hover);
            --bs-btn-active-color: #07111f;
            --bs-btn-active-bg: var(--jk-primary-hover);
            --bs-btn-active-border-color: var(--jk-primary-hover);
            font-weight: 750;
        }

        html[data-theme="dark"] .btn-outline-primary {
            --bs-btn-color: #93c5fd;
            --bs-btn-border-color: #60a5fa;
            --bs-btn-hover-color: #07111f;
            --bs-btn-hover-bg: #93c5fd;
            --bs-btn-hover-border-color: #93c5fd;
            --bs-btn-active-color: #07111f;
            --bs-btn-active-bg: #93c5fd;
            --bs-btn-active-border-color: #93c5fd;
        }

        html[data-theme="dark"] .btn-outline-danger,
        html[data-theme="dark"] .repeat-remove {
            color: #fca5a5;
            border-color: #f87171;
        }

        html[data-theme="dark"] .btn-outline-danger:hover,
        html[data-theme="dark"] .repeat-remove:hover {
            background: #f87171;
            color: #200707;
        }

        html[data-theme="dark"] .contact-btn,
        html[data-theme="dark"] .btn-submit,
        html[data-theme="dark"] .more-filters-count {
            color: var(--jk-primary-contrast);
        }

        html[data-theme="dark"] .map-pin-mode,
        html[data-theme="dark"] .favorite-button,
        html[data-theme="dark"] .fav-btn,
        html[data-theme="dark"] .ls-unfavorite {
            background: var(--jk-input-bg);
            border-color: #52627a;
            color: var(--jk-text);
        }

        html[data-theme="dark"] .map-pin-mode:hover,
        html[data-theme="dark"] .map-pin-mode.active {
            background: var(--jk-primary-subtle);
            border-color: var(--jk-primary);
            color: var(--jk-primary-dark);
        }

        html[data-theme="dark"] .favorite-button:hover,
        html[data-theme="dark"] .favorite-button.is-favorite,
        html[data-theme="dark"] .fav-btn:hover,
        html[data-theme="dark"] .fav-btn.active,
        html[data-theme="dark"] .ls-unfavorite:hover {
            background: #422006;
            border-color: #fbbf24;
            color: #fbbf24;
        }

        html[data-theme="dark"] .map-result-card:hover,
        html[data-theme="dark"] .map-result-card.active {
            background: var(--jk-surface-soft) !important;
            border-color: var(--card-color);
        }

        html[data-theme="dark"] button:disabled,
        html[data-theme="dark"] .btn:disabled,
        html[data-theme="dark"] input:disabled,
        html[data-theme="dark"] select:disabled,
        html[data-theme="dark"] textarea:disabled {
            opacity: .62;
        }

        html[data-theme="dark"] select option {
            background: var(--jk-input-bg);
            color: var(--jk-text);
        }
    </style>
</head>
<body>
    <x-site.header />

    <main class="site-main">
        @yield('content')
    </main>

    <x-site.footer />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const sync = () => {
                const theme = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
                button.setAttribute('aria-label', theme === 'dark' ? 'Schakel naar lichte modus' : 'Schakel naar donkere modus');
                button.setAttribute('title', button.getAttribute('aria-label'));
                button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            };

            button.addEventListener('click', () => {
                const current = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.dataset.theme = next;
                try {
                    localStorage.setItem('jongerenkaart.theme', next);
                } catch (error) {}
                sync();
            });

            sync();
        });

        document.querySelectorAll('[data-overflow-nav]').forEach((navigation) => {
            const moreItem = navigation.querySelector('[data-overflow-more]');
            const overflowMenu = moreItem?.querySelector('[data-overflow-menu]');
            const desktopQuery = window.matchMedia('(min-width: 768px)');

            if (!moreItem || !overflowMenu) {
                return;
            }

            const restoreItems = () => {
                Array.from(overflowMenu.querySelectorAll('[data-overflow-item]')).forEach((item) => {
                    navigation.insertBefore(item, moreItem);
                });
            };

            const syncActiveState = () => {
                const activeInMenu = Boolean(overflowMenu.querySelector('.nav-link.active'));
                const button = moreItem.querySelector('.dropdown-toggle');
                button.classList.toggle('active', activeInMenu);
                button.classList.toggle('fw-semibold', activeInMenu);
            };

            const fitNavigation = () => {
                restoreItems();
                moreItem.classList.add('d-none');
                syncActiveState();

                if (!desktopQuery.matches) {
                    return;
                }

                const availableWidth = navigation.parentElement.clientWidth;

                if (navigation.scrollWidth <= availableWidth) {
                    return;
                }

                moreItem.classList.remove('d-none');

                while (navigation.scrollWidth > availableWidth) {
                    const candidates = Array.from(navigation.children).filter(
                        (item) => item.matches('[data-overflow-item]'),
                    );
                    const item = candidates.at(-1);

                    if (!item) {
                        break;
                    }

                    overflowMenu.prepend(item);
                }

                const lastVisibleItem = Array.from(navigation.children)
                    .filter((item) => item.matches('[data-overflow-item]'))
                    .at(-1);

                if (lastVisibleItem?.classList.contains('auth-nav-divider')) {
                    overflowMenu.prepend(lastVisibleItem);
                }

                syncActiveState();
            };

            let resizeFrame;
            const scheduleFit = () => {
                cancelAnimationFrame(resizeFrame);
                resizeFrame = requestAnimationFrame(fitNavigation);
            };

            new ResizeObserver(scheduleFit).observe(navigation.parentElement);
            window.addEventListener('resize', scheduleFit, { passive: true });
            desktopQuery.addEventListener('change', scheduleFit);
            document.fonts?.ready.then(scheduleFit);
            scheduleFit();
        });
    </script>
    @stack('scripts')
</body>
</html>
