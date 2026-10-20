(() => {
  'use strict';

  // ---- Configuración del evento ----
  const EVENT = {
    start: new Date('2026-12-19T16:00:00-05:00'),
    end: new Date('2026-12-20T01:00:00-05:00'),
    deadline: new Date('2026-12-04T23:59:59-05:00'),
    title: 'Boda de Miguel Ángel & Daniela',
    place: 'Clásicos Forever - Salón Puente, Cl. 70 #33-376, Bucaramanga',
  };

  // ---- Datos del invitado (en el prototipo vienen por URL: ?n=Familia Pérez&c=3) ----
  const params = new URLSearchParams(location.search);
  const guest = {
    name: (params.get('n') || 'Nuestro invitado').slice(0, 60),
    seats: Math.min(Math.max(parseInt(params.get('c'), 10) || 3, 1), 10),
  };
  const storeKey = 'rsvp-demo:' + guest.name;

  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  $('#guestName').textContent = guest.name;
  $('#seatsN').textContent = guest.seats;
  $('#maxN').textContent = guest.seats;
  $('#seatsMsg').innerHTML = guest.seats === 1
    ? 'Tienes <b>1</b> cupo reservado para ti'
    : `Tienes <b>${guest.seats}</b> cupos reservados para ti`;

  // ---- Música (opcional: si no existe el archivo, el botón no aparece) ----
  const music = $('#music');
  const musicBtn = $('#musicBtn');
  music.addEventListener('error', () => { musicBtn.hidden = true; });
  musicBtn.addEventListener('click', () => {
    if (music.paused) { music.play(); musicBtn.classList.remove('muted'); }
    else { music.pause(); musicBtn.classList.add('muted'); }
  });

  // ---- Sobre ----
  const envelope = $('#envelope');
  $('#openBtn').addEventListener('click', () => {
    if (envelope.classList.contains('open')) return;
    envelope.classList.add('open');
    music.play().then(() => { musicBtn.hidden = false; }).catch(() => {});
    setTimeout(() => {
      envelope.classList.add('gone');
      document.body.classList.remove('locked');
      window.scrollTo(0, 0);
    }, 1900);
  });

  // ---- Cuenta atrás ----
  const pad = n => String(n).padStart(2, '0');
  function tick() {
    const diff = EVENT.start - new Date();
    if (diff <= 0) {
      ['d', 'h', 'm', 's'].forEach(k => { $('#cd-' + k).textContent = '00'; });
      $('#cd-note').textContent = '¡Hoy es el gran día!';
      return;
    }
    $('#cd-d').textContent = Math.floor(diff / 864e5);
    $('#cd-h').textContent = pad(Math.floor(diff / 36e5) % 24);
    $('#cd-m').textContent = pad(Math.floor(diff / 6e4) % 60);
    $('#cd-s').textContent = pad(Math.floor(diff / 1e3) % 60);
  }
  tick();
  setInterval(tick, 1000);

  // ---- Añadir al calendario (.ics generado en el navegador) ----
  $('#calBtn').addEventListener('click', () => {
    const fmt = d => d.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
    const ics = [
      'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Boda M&D//ES', 'BEGIN:VEVENT',
      'UID:boda-miguel-daniela-2026@miguelydaniela.site',
      'DTSTAMP:' + fmt(new Date()),
      'DTSTART:' + fmt(EVENT.start),
      'DTEND:' + fmt(EVENT.end),
      'SUMMARY:' + EVENT.title,
      'LOCATION:' + EVENT.place.replace(/,/g, '\\,'),
      'DESCRIPTION:Ceremonia religiosa 4:00 PM · Recepción 6:30 PM',
      'BEGIN:VALARM', 'TRIGGER:-P1D', 'ACTION:DISPLAY', 'DESCRIPTION:Mañana es la boda', 'END:VALARM',
      'END:VEVENT', 'END:VCALENDAR',
    ].join('\r\n');
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([ics], { type: 'text/calendar' }));
    a.download = 'boda-miguel-y-daniela.ics';
    a.click();
    setTimeout(() => URL.revokeObjectURL(a.href), 1000);
  });

  // ---- Aparecer al hacer scroll ----
  const io = 'IntersectionObserver' in window
    ? new IntersectionObserver(es => es.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
      }), { threshold: .15 })
    : null;
  $$('.reveal').forEach(el => io ? io.observe(el) : el.classList.add('in'));

  // ---- RSVP ----
  const steps = { choice: $('#stepChoice'), form: $('#stepForm'), yes: $('#stepYes'), no: $('#stepNo') };
  const show = name => Object.entries(steps).forEach(([k, el]) => { el.hidden = k !== name; });
  const attN = $('#attN');
  const namesBox = $('#names');
  let count = 1;

  function renderNames() {
    attN.textContent = count;
    $('#minus').disabled = count <= 1;
    $('#plus').disabled = count >= guest.seats;
    const prev = $$('input', namesBox).map(i => i.value);
    namesBox.innerHTML = '';
    for (let i = 0; i < count; i++) {
      const l = document.createElement('label');
      l.className = 'field';
      l.innerHTML = `<span>Nombre del asistente ${i + 1}</span><input type="text" maxlength="80" required autocomplete="off">`;
      $('input', l).value = prev[i] || (i === 0 && count === 1 ? '' : '');
      namesBox.appendChild(l);
    }
  }

  $('#minus').addEventListener('click', () => { if (count > 1) { count--; renderNames(); } });
  $('#plus').addEventListener('click', () => { if (count < guest.seats) { count++; renderNames(); } });

  $$('[data-choice]').forEach(b => b.addEventListener('click', () => {
    if (b.dataset.choice === 'yes') { count = Math.min(count, guest.seats); renderNames(); show('form'); }
    else { save({ status: 'no' }); showResult('no'); }
  }));
  $$('[data-back]').forEach(b => b.addEventListener('click', () => show('choice')));
  $$('[data-change]').forEach(b => b.addEventListener('click', () => { show('choice'); }));

  $('#stepForm').addEventListener('submit', e => {
    e.preventDefault();
    if ($('[name=website]').value) return; // honeypot anti-spam
    const names = $$('input', namesBox).map(i => i.value.trim());
    if (names.some(n => !n)) { $$('input', namesBox).find(i => !i.value.trim()).focus(); return; }
    save({ status: 'yes', attending: count, names, diet: $('#diet').value.trim(), msg: $('#msg').value.trim() });
    showResult('yes');
  });

  function save(data) {
    try { localStorage.setItem(storeKey, JSON.stringify({ ...data, at: Date.now() })); } catch (_) {}
  }

  function showResult(kind) {
    if (kind === 'yes') {
      $('#yesN').textContent = count === 1 ? '1 persona' : count + ' personas';
      show('yes');
      confettiHearts();
    } else {
      show('no');
    }
    steps[kind].scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function confettiHearts() {
    const box = $('.hearts');
    box.innerHTML = '';
    for (let i = 0; i < 18; i++) {
      const h = document.createElement('i');
      h.textContent = '♥';
      h.style.left = Math.random() * 100 + '%';
      h.style.fontSize = 14 + Math.random() * 18 + 'px';
      h.style.animationDelay = Math.random() * 1.2 + 's';
      box.appendChild(h);
    }
  }

  // Restaurar respuesta previa y cierre de plazo
  try {
    const saved = JSON.parse(localStorage.getItem(storeKey) || 'null');
    if (saved) {
      if (saved.status === 'yes') { count = saved.attending; showResult('yes'); }
      else showResult('no');
      window.scrollTo(0, 0);
    }
  } catch (_) {}

  if (new Date() > EVENT.deadline) {
    $('.muted b', $('#rsvp')).parentElement.textContent = 'El plazo para confirmar ya cerró. Escríbenos si necesitas algo.';
  }
})();
