# Manual de uso — Invitación de boda Miguel Ángel & Daniela

Este manual es para ustedes (los novios). No necesitan saber nada técnico.

---

## 1. Cómo funciona, en 30 segundos

1. Cada invitado (o familia) tiene **su propio link**, único e imposible de adivinar.
2. Lo reciben por WhatsApp, lo abren y ven **su nombre** y **cuántos cupos** tienen.
3. Confirman cuántos van (y los nombres), o dicen que no pueden.
4. Ustedes ven todo en tiempo real en el **panel**.

Nadie puede ver las invitaciones de otros, ni abrir la página sin un link válido.

## 2. Entrar al panel

- Dirección: **https://miguelydaniela.site/admin**
- Usuario: el correo que se les entregó (por defecto `admin@miguelydaniela.site`)
- Clave: la que se les entregó. Es **una sola cuenta compartida** entre ustedes dos.

> Guarden la clave en un gestor de contraseñas o en un lugar privado. No la envíen por WhatsApp en grupos.
> Si la olvidan, se restablece desde el servidor (ver la guía de despliegue).

Para salir: **Salir** (arriba a la derecha). En el celular funciona igual que en computador.

## 3. Cargar a los 80 invitados

### Opción A — Importar desde Excel (recomendado, 5 minutos)

1. En Excel o Google Sheets, hagan una tabla de **3 columnas** (sin más):

   | nombre | cupos | telefono |
   |---|---|---|
   | Familia Pérez | 3 | 3001234567 |
   | Juan Gómez | 1 | 3109876543 |

   - **nombre**: como quieren que aparezca en el sobre (*“Familia Pérez”*, *“Ana y Luis”*, *“Tía Marta”*).
   - **cupos**: cuántas personas puede traer esa invitación (1 a 20), **incluyendo al invitado**.
   - **telefono**: celular de WhatsApp, 10 dígitos. Es opcional; sin él igual pueden enviar el link
     eligiendo el contacto a mano.
2. Guarden como **CSV** (*Archivo → Descargar → Valores separados por comas* o *Guardar como → CSV*).
3. En el panel, bajen a **“Importar invitados desde CSV”**, elijan el archivo y toquen **Importar**.
4. Si alguna fila tiene un error (nombre vacío, cupos en 0…) se importan las demás y el panel les dice
   **en qué línea** está el problema. Corrijan esas filas y vuelvan a importar **solo esas** (para no duplicar).

> ⚠️ Importar dos veces el mismo archivo **duplica** las invitaciones. Si pasa, elimínenlas desde *Editar → Eliminar*.

### Opción B — Una por una

**+ Nueva invitación** → nombre, cupos y (opcional) teléfono → **Guardar**.

## 4. Enviar las invitaciones por WhatsApp

En la lista, cada invitado tiene tres botones:

- **WhatsApp** — abre WhatsApp con el mensaje ya escrito: *“Hola Familia Pérez, tenemos algo muy especial para ti: nuestra invitación de boda. Ábrela aquí: (su link)”*.
  Solo toquen **enviar**. Si no cargaron teléfono, WhatsApp les deja elegir el contacto.
- **Copiar link** — para pegarlo donde quieran (Instagram, correo…).
- **Editar** — cambiar nombre, cupos o teléfono, y ver su respuesta completa.

**Consejo:** envíen primero a 2 o 3 personas cercanas, pídanles que prueben confirmar y rechazar desde el celular,
y luego mandan al resto. Elimínen o reinicien esas pruebas antes de seguir.

## 5. Seguir las respuestas

Arriba del panel verán, siempre al día:

| Número | Significa |
|---|---|
| **Personas confirmadas** | Total de personas que irán (la cifra para el catering) |
| Invitaciones que aceptaron | Cuántas invitaciones/familias dijeron que sí |
| Sin responder | Aún no han contestado |
| No asistirán | Dijeron que no |
| Sin abrir el link | **Ni siquiera lo han abierto** (suele ser que no vieron el mensaje) |
| Cupos | Total de cupos entregados |

**Filtros:** el desplegable *Todos / Sin responder / Confirmaron / No asistirán / Sin abrir* y el buscador por nombre.

**Cada tarjeta** muestra el estado (*Pendiente, Confirmó, No asistirá*), cuántas personas irán, si el invitado
**abrió el link** y un 💬 si dejó un mensaje para ustedes.

### Seguimiento recomendado

1. Filtren **Sin abrir** → reenvíen el WhatsApp (probablemente no lo vieron).
2. Filtren **Sin responder** → recordatorio amable (la fecha límite es el **viernes 4 de diciembre**).
3. Después del 4 de diciembre: **Exportar** y entregar el listado final al salón/catering.

## 6. Ver el detalle de una respuesta

**Editar** en la tarjeta del invitado muestra: quiénes irán (nombres), restricciones de comida,
su mensaje, y cuándo abrió y respondió.

## 7. Situaciones comunes

| Situación | Qué hacer |
|---|---|
| El invitado se equivocó al responder | Puede **cambiar su respuesta él mismo** desde su link hasta el 4 de diciembre |
| Quiere cambiar después del 4 de diciembre | Entren a **Editar → Reiniciar respuesta** y que lo haga de nuevo (¡y avisen que es el último permiso!) |
| Invitaron a alguien más a una familia | **Editar** → suban los **cupos** |
| Quieren bajar cupos de quien ya confirmó | Primero **Reiniciar respuesta**; el sistema no deja bajar por debajo de lo confirmado |
| Se equivocaron en el nombre | **Editar** → corregir → *Guardar* (el link **no cambia**, no hay que reenviarlo) |
| Una invitación no debe existir | **Editar → Eliminar invitación**. El link deja de funcionar para siempre |
| Un invitado dice “no me abre” | Revisen que copiaron **el link completo**. Si lo escribió a mano, que lo pegue |
| Un invitado perdió el link | Botón **WhatsApp** o **Copiar link** de su tarjeta |

## 8. Exportar a Excel

**Exportar (Excel)** descarga un archivo `.csv` con **todas** las invitaciones: estado, personas, nombres,
restricciones, mensajes, link y fechas. Se abre con doble clic en Excel (con acentos correctos) o en Google Sheets
(*Archivo → Importar*). Es su **respaldo legible**: háganlo al menos el 4 de diciembre y una semana antes del evento.

## 9. Qué ve el invitado

1. Un **sobre** con su nombre; al tocar el sello, se abre y empieza la **música** (tiene botón para silenciarla).
2. La portada, la **cuenta atrás**, la historia, el **itinerario**, el lugar con botones para **Google Maps y Waze**
   y **añadir al calendario**, y el código de vestimenta.
3. **RSVP**: ve sus cupos y elige *Sí* (cuántos, nombres, comida, mensaje) o *No*.
   Si dice que no, aparece **Mia** con carita triste y un mensaje de cariño.
4. Si ya pasó el 4 de diciembre, ve un aviso de que el plazo cerró.

## 10. Cambiar el diseño

Hay dos versiones: **clásica** (nacarado y dorado) y **botánica** (papel verde y flores secas).
Para verlas con un link real, agreguen al final `?tema=botanico` o `?tema=clasico`. Cuando decidan,
pídanle a Luis fijar una (es un cambio de una línea en el servidor).

## 11. Preguntas rápidas

- **¿Puede alguien ver la lista completa de invitados?** No. Solo ustedes, con la clave del panel.
- **¿Google puede encontrar la invitación?** No: está marcada para que los buscadores la ignoren.
- **¿Qué pasa si dos personas de la misma familia usan el mismo link?** Es **una sola respuesta** por link; la
  última que se guarde es la que cuenta. Pídanles que lo confirmen entre ellos.
- **¿Se pierde algo si se cae el servidor?** Hagan respaldo (ver guía de despliegue) y exporten el CSV con frecuencia.
- **¿Se puede cambiar el mensaje de WhatsApp?** Sí, pídanle a Luis; es un texto configurable. Está sin emojis a propósito: WhatsApp de escritorio los muestra como “�” cuando el mensaje sale de un link.
- **¿Cómo se ve el link en WhatsApp?** Con una foto de ustedes, el título “Miguel Ángel & Daniela · Nuestra Boda” y la fecha. Si probaron antes con un link y no salía la foto, es que WhatsApp guarda en memoria la vista previa vieja: envíen una invitación **nueva** (otro link) para verla.

## 12. A quién acudir

Para cualquier cosa técnica (clave olvidada, cambiar el diseño, errores): **Luis**.
