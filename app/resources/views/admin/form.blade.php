@extends('admin.layout')
@php($editing = $guest->exists)
@section('title', $editing ? 'Editar invitación' : 'Nueva invitación')

@section('content')
<form method="POST" class="panel narrow"
      action="{{ $editing ? route('admin.guests.update', $guest) : route('admin.guests.store') }}">
  @csrf
  @if ($editing) @method('PUT') @endif
  <h1>{{ $editing ? 'Editar invitación' : 'Nueva invitación' }}</h1>

  <label>Nombre del invitado o grupo
    <input type="text" name="name" value="{{ old('name', $guest->name) }}" maxlength="255" required placeholder="Familia Pérez">
  </label>
  @error('name')<p class="error">{{ $message }}</p>@enderror

  <label>Cupos asignados
    <input type="number" name="seats" value="{{ old('seats', $guest->seats) }}" min="1" max="20" required>
  </label>
  @error('seats')<p class="error">{{ $message }}</p>@enderror

  <label>Teléfono / WhatsApp <em>(opcional)</em>
    <input type="text" name="phone" value="{{ old('phone', $guest->phone) }}" maxlength="30" placeholder="3001234567" inputmode="tel">
  </label>
  @error('phone')<p class="error">{{ $message }}</p>@enderror

  <div class="actions">
    <button class="btn" type="submit">Guardar</button>
    <a class="btn ghost" href="{{ route('admin.index') }}">Cancelar</a>
  </div>
</form>

@if ($editing)
  <section class="panel narrow">
    <h2>Invitación</h2>
    <p class="meta">Link personal:</p>
    <p><code class="url">{{ $guest->invitationUrl() }}</code></p>
    <div class="actions">
      <a class="btn wa" href="{{ $guest->whatsappUrl() }}" target="_blank" rel="noopener">Enviar por WhatsApp</a>
      <a class="btn ghost" href="{{ $guest->invitationUrl() }}" target="_blank" rel="noopener">Ver invitación</a>
    </div>
  </section>

  <section class="panel narrow">
    <h2>Respuesta</h2>
    <p><span class="badge {{ $guest->status->value }}">{{ $guest->status->label() }}</span>
      @if ($guest->responded_at) <span class="meta">· {{ $guest->responded_at->format('d/m/Y H:i') }}</span> @endif
    </p>
    @if ($guest->attending)
      <p><b>{{ $guest->attending }}</b> asistirán: {{ implode(', ', $guest->attendee_names ?? []) }}</p>
    @endif
    @if ($guest->diet)<p><b>Restricciones:</b> {{ $guest->diet }}</p>@endif
    @if ($guest->message)<p><b>Mensaje:</b> “{{ $guest->message }}”</p>@endif
    <p class="meta">
      @if ($guest->first_opened_at)
        Abrió el link por primera vez el {{ $guest->first_opened_at->format('d/m/Y H:i') }}; última vez el {{ $guest->last_opened_at->format('d/m/Y H:i') }}.
      @else
        Todavía no ha abierto el link.
      @endif
    </p>

    @if ($guest->status->value !== 'pending')
      <form method="POST" action="{{ route('admin.guests.reset', $guest) }}"
            onsubmit="return confirm('¿Reiniciar la respuesta? Volverá a quedar pendiente y se borran sus datos.')">
        @csrf
        <button class="btn ghost" type="submit">Reiniciar respuesta</button>
      </form>
    @endif
  </section>

  <section class="panel narrow danger">
    <h2>Eliminar</h2>
    <form method="POST" action="{{ route('admin.guests.destroy', $guest) }}"
          onsubmit="return confirm('¿Eliminar esta invitación? Su link dejará de funcionar y no se puede deshacer.')">
      @csrf @method('DELETE')
      <button class="btn danger" type="submit">Eliminar invitación</button>
    </form>
  </section>
@endif
@endsection
