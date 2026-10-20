@extends('admin.layout')
@section('title', 'Invitados')

@section('content')
<section class="stats">
  <div><b>{{ $stats['attending'] }}</b><span>personas confirmadas</span></div>
  <div><b>{{ $stats['accepted'] }}</b><span>invitaciones que aceptaron</span></div>
  <div><b>{{ $stats['pending'] }}</b><span>sin responder</span></div>
  <div><b>{{ $stats['declined'] }}</b><span>no asistirán</span></div>
  <div><b>{{ $stats['unopened'] }}</b><span>sin abrir el link</span></div>
  <div><b>{{ $stats['seats'] }}</b><span>cupos en {{ $stats['invitations'] }} invitaciones</span></div>
</section>

<section class="bar">
  <form method="GET" class="filters">
    <input type="search" name="q" value="{{ $q }}" placeholder="Buscar…">
    <select name="estado" onchange="this.form.submit()">
      <option value="">Todos</option>
      <option value="pending" @selected($status === 'pending')>Sin responder</option>
      <option value="accepted" @selected($status === 'accepted')>Confirmaron</option>
      <option value="declined" @selected($status === 'declined')>No asistirán</option>
      <option value="unopened" @selected($status === 'unopened')>Sin abrir</option>
    </select>
    <button class="btn ghost" type="submit">Filtrar</button>
  </form>
  <div class="actions">
    <a class="btn" href="{{ route('admin.guests.create') }}">+ Nueva invitación</a>
    <a class="btn ghost" href="{{ route('admin.export') }}">Exportar (Excel)</a>
  </div>
</section>

<section class="list">
  @forelse ($guests as $g)
    <article class="guest">
      <div class="guest__main">
        <h3>{{ $g->name }}</h3>
        <p class="meta">
          {{ $g->seats }} {{ $g->seats === 1 ? 'cupo' : 'cupos' }}
          @if ($g->phone) · {{ $g->phone }} @endif
        </p>
        <p>
          <span class="badge {{ $g->status->value }}">{{ $g->status->label() }}</span>
          @if ($g->attending) <span class="meta"><b>{{ $g->attending }}</b> asistirán</span> @endif
          @if ($g->first_opened_at)
            <span class="meta" title="Abrió el {{ $g->first_opened_at->format('d/m H:i') }}">· abrió el link</span>
          @else
            <span class="meta dim">· aún no abre el link</span>
          @endif
          @if ($g->message) <span title="{{ $g->message }}">💬</span> @endif
        </p>
      </div>
      <div class="guest__actions">
        <a class="btn wa" href="{{ $g->whatsappUrl() }}" target="_blank" rel="noopener">WhatsApp</a>
        <button class="btn ghost" type="button" data-copy="{{ $g->invitationUrl() }}">Copiar link</button>
        <a class="btn ghost" href="{{ route('admin.guests.edit', $g) }}">Editar</a>
      </div>
    </article>
  @empty
    <p class="empty">No hay invitados con ese filtro.</p>
  @endforelse
</section>

<section class="panel import">
  <h2>Importar invitados desde CSV</h2>
  <p class="meta">Columnas: <code>nombre;cupos;telefono</code> (encabezado opcional; sirve el CSV de Excel). Ejemplo: <code>Familia Pérez;3;3001234567</code></p>
  <form method="POST" action="{{ route('admin.import') }}" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".csv,.txt" required>
    <button class="btn" type="submit">Importar</button>
  </form>
  @error('file')<p class="error">{{ $message }}</p>@enderror
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-copy]').forEach(btn => btn.addEventListener('click', async () => {
  const url = btn.dataset.copy;
  try { await navigator.clipboard.writeText(url); }
  catch (_) { const t = document.createElement('textarea'); t.value = url; document.body.appendChild(t); t.select(); document.execCommand('copy'); t.remove(); }
  const old = btn.textContent; btn.textContent = '¡Copiado!'; setTimeout(() => btn.textContent = old, 1500);
}));
</script>
@endpush
