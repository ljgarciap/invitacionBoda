(() => {
  'use strict';

  // Estado del invitado, inyectado por el servidor (ver resources/views/invitation.blade.php)
  const INV = window.INVITATION;
  const START = new Date(INV.startsAt);
  const END = new Date(INV.endsAt);

  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  // ---- Música (opcional: si el archivo no existe, el botón no aparece) ----
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
    const diff = START - new Date();
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
      'DTSTART:' + fmt(START),
      'DTEND:' + fmt(END),
      'SUMMARY:Boda de Miguel Ángel & Daniela',
      'LOCATION:' + INV.venue.replace(/,/g, '\\,'),
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
  const formError = $('#formError');
  const submitBtn = $('#submitBtn');
  let count = Math.max(1, Math.min(INV.attending || 1, INV.seats));
  let prefillNames = INV.names || [];

  function renderNames() {
    attN.textContent = count;
    $('#minus').disabled = count <= 1;
    $('#plus').disabled = count >= INV.seats;
    const prev = $$('input', namesBox).map(i => i.value);
    namesBox.innerHTML = '';
    for (let i = 0; i < count; i++) {
      const l = document.createElement('label');
      l.className = 'field';
      l.innerHTML = `<span>Nombre del asistente ${i + 1}</span><input type="text" maxlength="80" required autocomplete="off">`;
      $('input', l).value = prev[i] ?? prefillNames[i] ?? '';
      namesBox.appendChild(l);
    }
  }

  function showError(msg) {
    formError.textContent = msg;
    formError.hidden = !msg;
  }

  async function send(payload) {
    const res = await fetch(INV.rsvpUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
      },
      body: JSON.stringify(payload),
    });
    const body = await res.json().catch(() => ({}));
    if (!res.ok) {
      const first = body.errors ? Object.values(body.errors)[0][0] : null;
      throw new Error(body.message && !body.errors ? body.message : (first || 'No pudimos guardar tu respuesta. Intenta de nuevo.'));
    }
    return body;
  }

  $('#minus').addEventListener('click', () => { if (count > 1) { count--; renderNames(); } });
  $('#plus').addEventListener('click', () => { if (count < INV.seats) { count++; renderNames(); } });

  $$('[data-choice]').forEach(b => b.addEventListener('click', async () => {
    showError('');
    if (b.dataset.choice === 'yes') {
      $('#diet').value = INV.diet || '';
      $('#msg').value = INV.message || '';
      renderNames();
      show('form');
      return;
    }
    b.disabled = true;
    try {
      await send({ status: 'declined' });
      INV.status = 'declined';
      showResult('no');
    } catch (err) {
      alert(err.message);
    } finally {
      b.disabled = false;
    }
  }));
  $$('[data-back]').forEach(b => b.addEventListener('click', () => show('choice')));
  $$('[data-change]').forEach(b => b.addEventListener('click', () => show('choice')));

  $('#stepForm').addEventListener('submit', async e => {
    e.preventDefault();
    showError('');
    const inputs = $$('input', namesBox);
    const names = inputs.map(i => i.value.trim());
    const empty = inputs.find(i => !i.value.trim());
    if (empty) { empty.focus(); showError('Escribe el nombre de cada asistente.'); return; }

    submitBtn.disabled = true;
    try {
      await send({
        status: 'accepted',
        attending: count,
        names,
        diet: $('#diet').value.trim() || null,
        message: $('#msg').value.trim() || null,
        website: $('[name=website]').value,
      });
      INV.status = 'accepted';
      INV.names = names;
      prefillNames = names;
      showResult('yes');
    } catch (err) {
      showError(err.message);
    } finally {
      submitBtn.disabled = false;
    }
  });

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

  // ---- Estado inicial según lo que ya respondió ----
  if (!INV.open) {
    // Plazo cerrado: ya no se puede cambiar la respuesta.
    $('#deadlineMsg').textContent = 'El plazo para confirmar ya cerró. Escríbenos si necesitas algo.';
    $$('[data-change]').forEach(b => b.remove());
    steps.choice.remove();
    delete steps.choice;
  }

  if (INV.status === 'accepted') {
    showResultSilent('yes');
  } else if (INV.status === 'declined') {
    showResultSilent('no');
  } else if (steps.choice) {
    show('choice');
  }

  function showResultSilent(kind) {
    if (kind === 'yes') $('#yesN').textContent = count === 1 ? '1 persona' : count + ' personas';
    Object.entries(steps).forEach(([k, el]) => { if (el) el.hidden = k !== kind; });
  }
})();
