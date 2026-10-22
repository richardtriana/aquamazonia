<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Aquamazonia') }}</title>

    <script src="{{ asset('js/app.js') }}" defer></script>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="{{ asset('css/fonts-google.css') }}" rel="stylesheet">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/all.css') }}" rel="stylesheet">
    <link href="{{ asset('css/fontawesome.css') }}" rel="stylesheet">
    <link href="{{ asset('css/brands.css') }}" rel="stylesheet">
    <link href="{{ asset('css/solid.css') }}" rel="stylesheet">
    <script src="{{ asset('js/shortcut.js') }}"></script>

    <style>
        body {
            margin: 0;
            background-color: #f5fafc;
        }

        /* Estructura general cuando hay sidebar */
        #app.app-with-sidebar {
            display: flex;
            min-height: 100vh;
        }

        /* Wrapper derecho del contenido */
        #app .app-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 0 1rem;
        }

        #app main {
            flex: 1;
        }

        /* Responsive: en móvil el sidebar NO deja margen fijo */
        @media (max-width: 767.98px) {
            #app.app-with-sidebar {
                flex-direction: column;
            }
        }

        /* ============================
            SPINNER GLOBAL
        ============================ */
        #global-spinner {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at top, #0ea5e9 0, #020617 40%, #020617 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        #global-spinner .global-spinner-inner {
            text-align: center;
            color: #e5e7eb;
        }

        #global-spinner .global-spinner-ring {
            width: 80px;
            height: 80px;
            border-radius: 999px;
            border: 4px solid rgba(148, 163, 184, 0.35);
            border-top-color: #22c55e;
            border-right-color: #0ea5e9;
            animation: global-spin 0.9s linear infinite;
            margin: 0 auto 1rem auto;
            box-shadow: 0 0 24px rgba(15, 23, 42, 0.8);
        }

        #global-spinner .global-spinner-logo {
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            font-size: 0.9rem;
            color: #f9fafb;
        }

        #global-spinner .global-spinner-text {
            margin-top: 0.25rem;
            font-size: 0.85rem;
            color: #9ca3af;
        }

        @keyframes global-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

</head>

<body>
    <div id="app" class="app-with-sidebar @auth app-with-sidebar-auth @endauth">

        {{-- SPINNER GLOBAL --}}
        <div id="global-spinner" class="global-spinner d-none">
            <div class="global-spinner-inner">
                <div class="global-spinner-ring"></div>
                <div class="global-spinner-logo">AQUAMAZONIA</div>
                <p class="global-spinner-text">Cargando datos, por favor espera...</p>
            </div>
        </div>

        @auth
            {{-- SIDEBAR --}}
            @component('components.sidebar-menu')
            @endcomponent

            {{-- CONTENIDO PRINCIPAL --}}
            <div class="app-content-wrapper">
                <main class="py-4">
                    @yield('content')
                </main>

                <footer class="text-primary text-center mb-3">
                    <small>Desarrollado por <b>ING RICHARD PEÑA</b></small>
                </footer>
            </div>
        @endauth

        @guest
            {{-- Vista sin autenticación --}}
            <main class="py-4 w-100">
                @yield('content')
            </main>
        @endguest

    </div>

    {{-- 🔑 PUENTE DE AUTENTICACIÓN PARA VUE --}}
    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sincronizamos el api_token de Richard Peña con el localStorage de Vue
            const apiToken = "{{ Auth::user()->api_token }}";
            if (apiToken) {
                localStorage.setItem('user_token', apiToken);
                console.log("Sesión de API vinculada correctamente para: {{ Auth::user()->name }}");
            }
        });
    </script>
    @endauth
</body>

</html>