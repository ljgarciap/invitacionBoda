@extends('admin.layout')
@section('title', 'Ingresar')

@section('content')
<form method="POST" action="{{ route('admin.login') }}" class="panel narrow">
  @csrf
  <h1>Ingresar</h1>
  <label>Correo
    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
  </label>
  <label>Clave
    <input type="password" name="password" required autocomplete="current-password">
  </label>
  @error('email')<p class="error">{{ $message }}</p>@enderror
  <label class="check"><input type="checkbox" name="remember" value="1"> Mantener la sesión iniciada</label>
  <button class="btn" type="submit">Entrar</button>
</form>
@endsection
