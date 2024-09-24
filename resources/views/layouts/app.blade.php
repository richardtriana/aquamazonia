<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}" />

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Aquamazonia') }}</title>

	<!-- Scripts -->
	<script src="{{ asset('js/app.js') }}" defer></script>

	<!-- Fonts -->
	<link rel="dns-prefetch" href="//fonts.gstatic.com">
	<link href="{{ asset('css/fonts-google.css') }}" rel="stylesheet">

	<!-- Styles -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/all.css') }}" rel="stylesheet">
	<link href="{{ asset('css/fontawesome.css') }}" rel="stylesheet">
	<link href="{{ asset('css/brands.css') }}" rel="stylesheet">
	<link href="{{ asset('css/solid.css') }}" rel="stylesheet">
    <script src="{{ asset('js/shortcut.js') }} "></script>

</head>

<body>
	<div id="app">
		<!-- Mostrar la barra de navegación solo si el usuario está autenticado -->
		@auth
			<!-- Aquí se coloca el código de la barra o el menú -->
			@component('components.sidebar-menu')
			@endcomponent
		@endauth

		<main class="py-4">
			@yield('content')
		</main>

		<!-- Mostrar el pie de página solo si el usuario está autenticado -->
		@auth
		<footer class="text-primary text-center">
			<small>
				Desarrollado por <b> ING RICHARD PEÑA By ING ISABEL SALAZAR </b>
			</small>
		</footer>
		@endauth
	</div>
</body>

</html>
