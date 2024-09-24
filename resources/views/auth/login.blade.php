@extends('layouts.app')

@section('content')
<div style="background: url('{{ asset('img/mar.jpg') }}') no-repeat center center fixed; background-size: cover; height: 100vh; display: flex; justify-content: center; align-items: center;">
    <div style="background-color: rgba(255, 255, 255, 0.8); border-radius: 12px; padding: 40px; width: 350px; box-shadow: 0 0 20px rgba(0, 0, 255, 0.5);">
        <h2 style="color: #0056b3; text-align: center; font-family: 'Orbitron', sans-serif; text-transform: uppercase; margin-bottom: 30px;">Ingresar Ver 2.9</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group" style="margin-bottom: 20px;">
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required autofocus style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                @error('email')
                    <span class="invalid-feedback" role="alert" style="color: red;">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Contraseña" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                @error('password')
                    <span class="invalid-feedback" role="alert" style="color: red;">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group mb-0">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px; background-color: #0056b3; border: none; color: white; border-radius: 5px;">
                    {{ __('Ingresar') }}
                </button>
            </div>

            <div style="text-align: center; margin-top: 15px;">
                @if (Route::has('password.request'))
                    <a class="btn btn-link" href="{{ route('password.request') }}" style="color: #0056b3;">
                        {{ __('¿Olvidaste tu contraseña?') }}
                        <br>
                        <b>Desarrollado por Ing Richard peña By Ing Isabel salazar </b>

                    </a>
                @endif
                
            </div>
        </form>
    </div>
</div>
@endsection
