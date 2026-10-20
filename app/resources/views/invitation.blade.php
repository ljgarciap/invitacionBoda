@php($a = fn (string $p) => asset('invitacion/'.$p))
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Miguel Ángel & Daniela · Nuestra Boda</title>
  <meta name="description" content="Acompáñanos a celebrar nuestra boda el 19 de diciembre de 2026.">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Miguel Ángel & Daniela">
  <meta property="og:locale" content="es_CO">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="Miguel Ángel & Daniela · Nuestra Boda">
  <meta property="og:description" content="Tienes una invitación especial. 19 de diciembre de 2026 · Bucaramanga. Ábrela y confirma tu asistencia.">
  <meta property="og:image" content="{{ $a('img/og.jpg') }}">
  <meta property="og:image:type" content="image/jpeg">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="Miguel Ángel y Daniela con su perrita Mia frente a una cascada: Nuestra Boda">
  <meta name="twitter:card" content="summary_large_image">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Jost:wght@300;400;500&family=Pinyon+Script&family=Allura&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ $a('css/styles.css') }}">
  <link rel="stylesheet" href="{{ $a('css/tema-botanico.css') }}">
  <link rel="stylesheet" href="{{ $a('css/tema-verde.css') }}">
</head>
<body class="locked" data-theme="{{ $theme }}">


<!-- Ramas de flores secas (solo se ven en el tema botánico) -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="sprig" viewBox="0 0 200 260">
    <g fill="none" stroke="#8a7a45" stroke-width="3" stroke-linecap="round">
      <path d="M16 6C38 70 92 132 152 252"/>
      <path d="M44 56C70 48 98 58 120 86"/>
      <path d="M70 100C96 92 124 104 142 134"/>
      <path d="M30 28C14 40 8 62 12 84"/>
    </g>
    <g fill="#a99b5c" opacity=".9">
      <ellipse cx="60" cy="40" rx="22" ry="7" transform="rotate(-28 60 40)"/>
      <ellipse cx="96" cy="150" rx="22" ry="7" transform="rotate(38 96 150)"/>
      <ellipse cx="118" cy="196" rx="20" ry="6" transform="rotate(52 118 196)"/>
      <ellipse cx="22" cy="104" rx="18" ry="6" transform="rotate(80 22 104)"/>
    </g>
    <g fill="#f7ecd2">
      <circle cx="120" cy="86" r="9"/><circle cx="132" cy="76" r="7"/><circle cx="110" cy="74" r="7"/><circle cx="128" cy="96" r="7"/>
      <circle cx="142" cy="134" r="9"/><circle cx="154" cy="124" r="7"/><circle cx="132" cy="124" r="7"/><circle cx="150" cy="146" r="7"/>
      <circle cx="12" cy="86" r="8"/><circle cx="22" cy="76" r="6"/><circle cx="6" cy="76" r="6"/>
      <circle cx="84" cy="52" r="7"/><circle cx="96" cy="60" r="6"/><circle cx="100" cy="112" r="7"/><circle cx="112" cy="104" r="6"/>
    </g>
    <g fill="#d9b564">
      <circle cx="120" cy="86" r="2.6"/><circle cx="142" cy="134" r="2.6"/><circle cx="12" cy="86" r="2.4"/><circle cx="84" cy="52" r="2.2"/><circle cx="100" cy="112" r="2.2"/>
    </g>
  </symbol>
</svg>

<!-- ============ SOBRE ============ -->
<div id="envelope" class="envelope" role="dialog" aria-label="Invitación cerrada">
  <div class="envelope__bg"></div>
  <svg class="deco deco--tl" aria-hidden="true"><use href="#sprig"/></svg>
  <svg class="deco deco--br" aria-hidden="true"><use href="#sprig"/></svg>
  <div class="torn" aria-hidden="true"></div>
  <div class="envelope__stage">
    <p class="envelope__pre">Una invitación especial para</p>
    <h2 class="envelope__guest" id="guestName">{{ $guest->name }}</h2>
    <button class="envelope__box" id="openBtn" aria-label="Abrir invitación">
      <span class="envelope__back"></span>
      <span class="envelope__card"><span>Miguel Ángel<br>&amp;<br>Daniela</span></span>
      <span class="envelope__front"></span>
      <span class="envelope__flap"></span>
      <span class="envelope__seal">M&amp;D</span>
    </button>
    <p class="envelope__hint">Toca el sello para abrir</p>
  </div>
</div>

<!-- ============ CONTROL DE MÚSICA ============ -->
<button id="musicBtn" class="music" aria-label="Silenciar música" hidden>
  <svg viewBox="0 0 24 24" aria-hidden="true"><path class="on" d="M3 10v4h4l5 4V6L7 10H3zm13.5 2a4.5 4.5 0 0 0-2.5-4v8a4.5 4.5 0 0 0 2.5-4zM14 3.2v2.1a7 7 0 0 1 0 13.4v2.1a9 9 0 0 0 0-17.6z"/><path class="off" d="M3 10v4h4l5 4V6L7 10H3zm13.6 2 2.4-2.4-1.4-1.4-2.4 2.4-2.4-2.4-1.4 1.4 2.4 2.4-2.4 2.4 1.4 1.4 2.4-2.4 2.4 2.4 1.4-1.4z"/></svg>
</button>
<audio id="music" src="{{ $a('audio/cancion.mp3') }}" loop preload="none"></audio>

<main id="page" class="page">

  <!-- ============ PORTADA ============ -->
  <header class="hero" id="inicio">
    <img class="hero__img" src="{{ $a('img/portada.webp') }}" alt="Miguel Ángel y Daniela con su perrita Mia frente a una cascada. Nuestra boda: acompáñanos a celebrar." width="1600" height="1200" fetchpriority="high">
    <div class="hero__text reveal">
      <p class="eyebrow">Nos casamos</p>
      <h1 class="script">Miguel Ángel <i>&amp;</i> Daniela</h1>
      <p class="hero__date">Sábado · 19 de diciembre de 2026 · 4:00 pm</p>
      <a href="#rsvp" class="btn btn--gold">Confirmar asistencia</a>
    </div>
  </header>

  <!-- ============ CUENTA ATRÁS ============ -->
  <section class="section section--pearl" id="cuenta">
    <div class="wrap narrow center reveal">
      <blockquote class="verse">
        <p>“Sobre todas estas cosas vestíos de amor, que es el vínculo perfecto”.</p>
        <cite>Colosenses 3:14</cite>
        <svg class="heart heart--sm" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5C5 16 2.5 12.600 2.500 9.200 2.500 6.700 4.400 5 6.700 5c2.200 0 3.800 1.300 5.300 3.200C13.500 6.300 15.100 5 17.300 5c2.300 0 4.200 1.700 4.200 4.200 0 3.400-2.500 6.800-9.500 11.300z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
        <p class="verse__wish">Que Dios sea siempre el centro de nuestra historia.</p>
      </blockquote>
      <p class="eyebrow">Faltan</p>
      <div class="countdown" id="countdown" aria-live="off">
        <div><b id="cd-d">--</b><span>días</span></div>
        <div><b id="cd-h">--</b><span>horas</span></div>
        <div><b id="cd-m">--</b><span>min</span></div>
        <div><b id="cd-s">--</b><span>seg</span></div>
      </div>
      <p class="muted" id="cd-note">para el gran día</p>
      <div class="envelopes-note">
        <p class="eyebrow">Lluvia de sobres</p>
        <p>Tu presencia es nuestro mejor regalo. Si deseas obsequiarnos algo, tendremos una lluvia de sobres.</p>
      </div>
    </div>
  </section>

  <!-- ============ HISTORIA ============ -->
  <section class="section" id="historia">
    <svg class="deco deco--tl" aria-hidden="true"><use href="#sprig"/></svg>
    <div class="wrap">
      <div class="center reveal">
        <p class="eyebrow">Nuestra historia</p>
        <h2 class="script h2">Dos caminos, un mismo destino</h2>
      </div>
      <div class="story">
        <figure class="story__item reveal">
          <img src="{{ $a('img/barichara.webp') }}" alt="Miguel Ángel y Daniela en una calle empedrada de Barichara, con buganvilias" loading="lazy" width="1000" height="1333">
          <figcaption>Caminando juntos, sin afán.</figcaption>
        </figure>
        <figure class="story__item story__item--down reveal">
          <img src="{{ $a('img/cafe.webp') }}" alt="Miguel Ángel y Daniela desayunando frente a una cascada" loading="lazy" width="1000" height="1250">
          <figcaption>Y un buen café de por medio.</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <!-- ============ ITINERARIO ============ -->
  <section class="section section--pearl" id="itinerario">
    <div class="wrap narrow">
      <div class="center reveal">
        <svg class="heart" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5C5 16 2.5 12.600 2.500 9.200 2.500 6.700 4.400 5 6.700 5c2.200 0 3.800 1.300 5.300 3.200C13.500 6.300 15.100 5 17.300 5c2.300 0 4.200 1.700 4.200 4.200 0 3.400-2.500 6.800-9.500 11.300z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
        <h2 class="script h2">Nuestro gran día</h2>
        <p class="muted">El comienzo de nuestro para siempre</p>
      </div>
      <ol class="timeline">
        <li class="reveal"><time>4:00 PM</time><span>Ceremonia</span></li>
        <li class="reveal"><time>6:30 PM</time><span>Bienvenida &amp; cóctel</span></li>
        <li class="reveal"><time>7:10 PM</time><span>Entrada de los novios</span></li>
        <li class="reveal"><time>7:20 PM</time><span>Nuestro primer baile</span></li>
        <li class="reveal"><time>8:15 PM</time><span>Momentos especiales</span></li>
        <li class="reveal"><time>9:30 PM</time><span>Cena</span></li>
        <li class="reveal"><time>11:00 PM</time><span>Hora loca</span></li>
        <li class="reveal"><time>1:00 AM</time><span>Fin de nuestra celebración</span></li>
      </ol>
      <p class="center timeline__close reveal">¡Prepárate para una noche llena de amor, sorpresas y mucha fiesta!</p>
    </div>
  </section>

  <!-- ============ LUGAR ============ -->
  <section class="section" id="lugar">
    <svg class="deco deco--tr" aria-hidden="true"><use href="#sprig"/></svg>
    <div class="wrap narrow center">
      <div class="reveal">
        <p class="eyebrow">Dónde</p>
        <h2 class="script h2">Clásicos Forever</h2>
        <p class="venue">Salón Puente</p>
        <address>
          <a href="{{ config('wedding.venue.maps_url') }}" target="_blank" rel="noopener">
            {{ config('wedding.venue.address') }}
          </a>
          <small>300 metros por la Vía a Las Colinas</small>
        </address>
        <div class="actions">
          <a class="btn btn--gold" href="{{ config('wedding.venue.maps_url') }}" target="_blank" rel="noopener">Abrir en Google Maps</a>
          <a class="btn btn--ghost" href="https://waze.com/ul?ll={{ config('wedding.venue.lat') }},{{ config('wedding.venue.lng') }}&navigate=yes" target="_blank" rel="noopener">Abrir en Waze</a>
          <button class="btn btn--ghost" id="calBtn" type="button">Añadir al calendario</button>
        </div>
      </div>
      <div class="map reveal">
        <iframe title="Mapa del lugar" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
          src="https://www.google.com/maps?q={{ urlencode(config('wedding.venue.name').', '.config('wedding.venue.address')) }}&ll={{ config('wedding.venue.lat') }},{{ config('wedding.venue.lng') }}&z=17&output=embed"></iframe>
      </div>
      <div class="dress reveal">
        <p class="eyebrow">Código de vestimenta</p>
        <p class="serif-lg">Ven como te sientas más cómod@.</p>
      </div>
    </div>
  </section>

  <!-- ============ RSVP ============ -->
  <section class="section section--pearl rsvp" id="rsvp">
    <div class="wrap narrow">
      <div class="center reveal">
        <p class="eyebrow">RSVP</p>
        <h2 class="script h2">¿Nos acompañas?</h2>
        <p class="seats" id="seatsMsg">
          @if ($guest->seats === 1)
            Tienes <b>1</b> cupo reservado para ti
          @else
            Tienes <b>{{ $guest->seats }}</b> cupos reservados para ti
          @endif
        </p>
        <p class="muted" id="deadlineMsg">Por favor confirma antes del <b>viernes 4 de diciembre</b>.</p>
      </div>

      <!-- Paso 1: elegir -->
      <div class="choice reveal" id="stepChoice">
        <button class="btn btn--gold" data-choice="yes">¡Sí, ahí estaremos!</button>
        <button class="btn btn--ghost" data-choice="no">No podremos asistir</button>
      </div>

      <!-- Paso 2: formulario -->
      <form class="card" id="stepForm" hidden novalidate>
        <label class="field">
          <span>¿Cuántas personas asistirán?</span>
          <div class="stepper">
            <button type="button" id="minus" aria-label="Menos">−</button>
            <output id="attN">1</output>
            <button type="button" id="plus" aria-label="Más">+</button>
          </div>
          <small>Máximo <span id="maxN">{{ $guest->seats }}</span></small>
        </label>
        <div id="names"></div>
        <label class="field">
          <span>Restricciones de comida o alergias <em>(opcional)</em></span>
          <input type="text" id="diet" maxlength="200" placeholder="Ej. vegetariano, alergia a mariscos…">
        </label>
        <label class="field">
          <span>Un mensaje para los novios <em>(opcional)</em></span>
          <textarea id="msg" rows="3" maxlength="500" placeholder="Escribe aquí…"></textarea>
        </label>
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
        <p class="form-error" id="formError" role="alert" hidden></p>
        <div class="actions">
          <button class="btn btn--gold" type="submit" id="submitBtn">Confirmar</button>
          <button class="btn btn--link" type="button" data-back>Volver</button>
        </div>
      </form>

      <!-- Resultado: aceptó -->
      <div class="result card center" id="stepYes" hidden>
        <img class="mia mia--happy" src="{{ $a('img/mia.webp') }}" alt="Mia, feliz" width="330" height="486">
        <h3 class="script">¡Qué alegría!</h3>
        <p>Mia ya movió la cola. Confirmaste <b id="yesN"></b>.<br>Nos vemos el 19 de diciembre.</p>
        <button class="btn btn--link" data-change>Cambiar mi respuesta</button>
        <div class="hearts" aria-hidden="true"></div>
      </div>

      <!-- Resultado: rechazó -->
      <div class="result card center" id="stepNo" hidden>
        <div class="mia-wrap mia-wrap--sad"><img class="mia mia--sad" src="{{ $a('img/mia.webp') }}" alt="Mia, triste" width="330" height="486"></div>
        <h3 class="script">Te vamos a extrañar</h3>
        <p>Mia puso carita de tristeza, pero entendemos.<br>Gracias por avisarnos y por tanto cariño.</p>
        <button class="btn btn--link" data-change>Cambié de opinión</button>
      </div>
    </div>
  </section>

  <footer class="footer center">
    <p class="script">¡Gracias por ser parte de nuestra historia!</p>
    <p class="muted">Miguel Ángel &amp; Daniela · 19.12.2026</p>
    <div class="torn" aria-hidden="true"></div>
  </footer>
</main>

<script>window.INVITATION = @json($state);</script>
<script src="{{ $a('js/app.js') }}"></script>
</body>
</html>
