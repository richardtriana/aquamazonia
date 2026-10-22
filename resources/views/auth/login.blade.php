@extends('layouts.app')

@section('content')
<div class="login-page">

    {{-- Título principal de la página --}}
    <div class="login-page-title text-center">
        <h1>Software de producción piscícola</h1>
    </div>

    {{-- Capa (actualmente desactivada por CSS) --}}
    <div class="login-bg-particles"></div>

    {{-- Tarjeta de login --}}
    <div class="login-card">
        <div class="login-title-wrapper text-center mb-4">
            <h2 class="login-title">Ingresar</h2>
            {{-- Solo nombre de la plataforma --}}
            <p class="login-subtitle">Aquamazonia</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Correo --}}
            <div class="form-group mb-3">
                <label for="email" class="login-label">Correo electrónico</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="form-control login-input @error('email') is-invalid @enderror"
                    placeholder="Ingresa tu correo"
                >
                @error('email')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div class="form-group mb-3">
                <label for="password" class="login-label">Contraseña</label>

                <div class="password-wrapper">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="form-control login-input @error('password') is-invalid @enderror"
                        placeholder="Ingresa tu contraseña"
                    >
                    <button
                        type="button"
                        class="toggle-password"
                        onclick="togglePasswordVisibility()"
                        aria-label="Mostrar u ocultar contraseña"
                    >
                        <i id="togglePasswordIcon" class="fas fa-eye"></i>
                    </button>
                </div>

                @error('password')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Botón ingresar --}}
            <div class="form-group mb-3">
                <button type="submit" class="btn login-btn w-100">
                    Ingresar
                </button>
            </div>

            {{-- Olvidaste contraseña --}}
            <div class="text-center mt-2">
                @if (Route::has('password.request'))
                    <a class="login-link" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            {{-- Firma --}}
            <div class="login-footer text-center mt-3">
                <small>Desarrollado por <strong>Ing Richard Peña</strong></small>
            </div>
        </form>
    </div>
</div>

{{-- Script del ojo de contraseña --}}
<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('togglePasswordIcon');

        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
