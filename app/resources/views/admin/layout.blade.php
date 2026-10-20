<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Panel') · Boda M&D</title>
  <link rel="stylesheet" href="{{ asset('invitacion/css/admin.css') }}">
</head>
<body>
<header class="top">
  <a href="{{ route('admin.index') }}" class="brand">Boda <b>M&amp;D</b> · Panel</a>
  @auth
    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="link" type="submit">Salir</button></form>
  @endauth
</header>
<main class="wrap">
  @if (session('ok'))
    <div class="flash ok" role="status">{{ session('ok') }}</div>
  @endif
  @if (session('import_errors'))
    <div class="flash warn" role="alert">
      <b>Algunas filas no se importaron:</b>
      <ul>@foreach (session('import_errors') as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif
  @yield('content')
</main>
@stack('scripts')
</body>
</html>
