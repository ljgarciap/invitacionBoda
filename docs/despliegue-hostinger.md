# Guía de despliegue en Hostinger (hosting compartido)

Dominio: `miguelydaniela.site` · Stack: Laravel 13, PHP 8.4, MySQL/MariaDB · Repo: `github.com/ljgarciap/invitacionBoda`

> **Convenciones.** Los comandos con `$` se ejecutan en el **servidor por SSH**. Los marcados con 🧑 los ejecuta
> una persona (Luis), no se automatizan. Donde un dato depende de tu plan de Hostinger y no se pudo comprobar
> desde aquí, está marcado con ⚠️ **verificar**.

---

## 0. Antes de empezar (checklist)

- [ ] El dominio `miguelydaniela.site` está agregado al plan en hPanel y apunta a Hostinger.
- [ ] Tienes acceso SSH (hPanel → *Avanzado* → *Acceso SSH*): host, puerto y usuario. Anótalos.
- [ ] La versión de PHP **de la web** es **8.4**: hPanel → *Sitios web* → `miguelydaniela.site` →
      *Avanzado* → *Configuración de PHP*. **Se configura por dominio y es independiente del PHP de SSH**:
      aunque `php -v` por SSH diga 8.4, la web puede seguir en otra versión (nos pasó: la web estaba en 8.3
      y todo daba 500 sin ningún log, porque las dependencias exigen 8.4). Verifícalo **antes de empezar**.
      Extensiones necesarias (activas por defecto casi siempre): `pdo_mysql`, `mbstring`, `openssl`,
      `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`.
- [ ] El repositorio es accesible desde el servidor (ver paso 3).

## 1. Crear la base de datos

hPanel → *Bases de datos* → *Administración de bases de datos MySQL* → crear base de datos + usuario.
Anota **nombre de la base, usuario y contraseña** (Hostinger les antepone un prefijo, p. ej. `u123456789_boda`).
Úsalos tal cual aparecen.

## 2. Conectarse por SSH y revisar el entorno

```bash
$ ssh -p <PUERTO> <USUARIO>@<HOST>
$ php -v          # debe decir PHP 8.4.x
$ composer -V     # debe existir (Hostinger lo trae instalado)
$ git --version
```

⚠️ **verificar** — Si `php -v` muestra otra versión en SSH aunque en hPanel pusiste 8.4, busca el binario
correcto (por ejemplo `ls /opt/alt/ | grep php`) y úsalo como `php8.4 artisan ...` y
`php8.4 $(which composer) ...`. Si no aparece, escribe a soporte de Hostinger pidiendo PHP 8.4 por CLI.

## 3. Descargar el código

El proyecto vive **fuera** de `public_html` (así `.env`, `vendor/` y el código nunca quedan expuestos).

```bash
$ cd ~/domains/miguelydaniela.site          # ⚠️ verificar la ruta (ls ~/domains)
$ git clone https://github.com/ljgarciap/invitacionBoda.git invitacion
```

Si el repo es **privado**, `git clone` pedirá credenciales: usa un *Personal Access Token* de GitHub
como contraseña, o configura una *deploy key* de solo lectura. Alternativa sin Git: comprimir la carpeta
`app/` (sin `vendor/` ni `.env`) y subirla por el Administrador de archivos.

## 4. Instalar dependencias (sin las de desarrollo)

En el CLI de Hostinger PHP tiene **desactivadas** varias funciones (`proc_open`, `exec`, `shell_exec`,
`symlink`…). Por eso `composer install` normal falla al final con *"The Process class relies on proc_open"*.
Se instala sin scripts y se quita la restricción **solo en los comandos `artisan`** con `-d disable_functions=`:

```bash
$ cd ~/domains/miguelydaniela.site/invitacion/app
$ composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
$ php -d disable_functions= artisan package:discover --ansi
$ alias art='php -d disable_functions= artisan'     # válido solo en esta sesión SSH
```

En el resto de la guía, `art` equivale a `php artisan` con ese prefijo. (La web no necesita esas funciones.)

No hay build de Node: los estilos, scripts, imágenes y la canción ya están en `public/invitacion/`.

## 5. Configurar el `.env`

```bash
$ cp .env.production.example .env
$ nano .env
```

Completa: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (paso 1). El resto ya viene correcto
(`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://miguelydaniela.site`).

```bash
$ art key:generate --force      # genera APP_KEY
```

> ⚠️ **Haz una copia del `.env` completo en tu gestor de claves.** Si se pierde `APP_KEY` se invalidan
> las sesiones; la base de datos no se afecta.

## 6. Permisos de escritura

```bash
$ chmod -R 775 storage bootstrap/cache
```

## 7. Base de datos y usuario del panel

```bash
$ art migrate --force
$ art admin:password            # pide la clave dos veces, sin mostrarla
```

`admin:password` crea (o actualiza) el usuario único del panel: correo `admin@miguelydaniela.site`
(o `art admin:password otro@correo.com`). La clave debe tener **mínimo 12 caracteres**.
**No uses `db:seed` en producción** (es solo para datos de muestra locales).

## 8. Publicar: apuntar el dominio a `public/`

Hostinger sirve el sitio desde `public_html`. Se reemplaza por un enlace simbólico a `public/` de Laravel,
**conservando una copia** de lo que había:

```bash
$ cd ~/domains/miguelydaniela.site
$ mv public_html public_html_original      # respaldo, no se borra nada
$ ln -s invitacion/app/public public_html
$ ls -la public_html/                       # debe listar index.php, .htaccess, invitacion/
```

⚠️ **verificar** — Algunos planes permiten además cambiar la carpeta raíz del dominio desde hPanel; si
existe esa opción (*Sitios web → Administrar → Avanzado*), apunta a `invitacion/app/public` y omite el enlace.
Si el enlace simbólico no se sirve (error 403/404 en todo el sitio), usa la opción de cambiar la raíz o
escribe a soporte.

## 9. HTTPS

hPanel → *Seguridad* → *SSL* → instalar el certificado gratuito para `miguelydaniela.site` y activar
**“Forzar HTTPS”**. La app ya fuerza `https` en los enlaces en producción.

## 10. Optimizar (solo después de dejar el `.env` final)

```bash
$ cd ~/domains/miguelydaniela.site/invitacion/app
$ art config:cache && art route:cache && art view:cache
```

> Después de **cualquier cambio en `.env`** hay que correr `art config:clear` y volver a cachear,
> si no, el cambio no se ve.

## 11. Verificación (smoke test)

| Prueba | Resultado esperado |
|---|---|
| `https://miguelydaniela.site/` | **404** (es a propósito: la raíz no muestra nada) |
| `https://miguelydaniela.site/admin/login` | Pantalla de login |
| Entrar con el usuario del paso 7 | Panel con las estadísticas en 0 |
| Crear una invitación de prueba y abrir su link | Sobre, música y página completa |
| Confirmar/rechazar desde el celular | Aparece en el panel; al recargar se conserva |
| Link inexistente `/i/zzzzzzzz` | “Invitación no encontrada” |
| Revisar que la canción suene al abrir el sobre | Sí (en iPhone Safari también, tras tocar el sello) |

Luego **elimina la invitación de prueba** desde el panel.

## 12. Elegir el diseño definitivo

Compara ambos con un link real agregando `?tema=clasico` o `?tema=botanico`. Para fijar uno para todos:
`WEDDING_THEME=botanico` en el `.env` → `art config:clear && art config:cache`.

## 13. Cargar a los invitados

Desde el panel: *Importar CSV* (`nombre;cupos;telefono`) o crear uno por uno. Luego el botón **WhatsApp**
de cada tarjeta. Ver el [manual de uso](manual-de-uso.md).

---

## Actualizar la aplicación (cambios futuros)

```bash
$ cd ~/domains/miguelydaniela.site/invitacion/app
$ git pull
$ composer install --no-dev --optimize-autoloader --no-interaction
$ art migrate --force
$ art config:cache && art route:cache && art view:cache
```

## Respaldos (importante: es un dato que no se puede recrear)

- **Base de datos:** hPanel → *Bases de datos* → phpMyAdmin → *Exportar*, o por SSH:
  `mysqldump -u <USUARIO> -p <BASE> > respaldo-$(date +%F).sql`. Hazlo **antes del 4 de diciembre, el día
  siguiente y la semana del evento**, y guarda el archivo fuera del servidor.
- **CSV de respuestas:** botón *Exportar (Excel)* del panel. Es un respaldo legible por humanos.

## Problemas frecuentes

| Síntoma | Causa probable | Qué hacer |
|---|---|---|
| Error 500 en todo y **no existe** `storage/logs/laravel.log` | La **web** usa PHP < 8.4 (la app ni arranca) | hPanel → Configuración de PHP del dominio → 8.4. Comprobar con un `v.php` temporal que imprima `PHP_VERSION` y **borrarlo después** |
| Error 500 en todo, con log | Permisos o `.env` mal | `tail -50 storage/logs/laravel.log`; revisar paso 6 |
| `Process class relies on proc_open` al instalar o usar `artisan` | CLI con funciones bloqueadas | Usar `php -d disable_functions= artisan ...` (paso 4) |
| “No application encryption key” | Falta `APP_KEY` | `art key:generate --force` y recachear |
| Error de conexión a la base | Datos o host incorrectos | Revisar `DB_*`; probar `DB_HOST=localhost` en vez de `127.0.0.1` (o al revés) |
| 404 en todas las rutas menos `/` | `.htaccess` no se lee / enlace roto | Confirmar `ls -la public_html/` (paso 8) |
| Estilos/imágenes no cargan | La raíz no es `public/` | Repetir paso 8 |
| Cambié el `.env` y no pasa nada | Config cacheada | `art config:clear` y cachear de nuevo |
| Olvidé la clave del panel | — | `art admin:password` |
| Muchos intentos de login | Límite de 5 por minuto (a propósito) | Esperar 1 minuto |

## Seguridad: resumen de lo que ya está resuelto

`APP_DEBUG=false` · login con límite de intentos · links con código aleatorio no adivinable ·
`noindex` en todas las páginas · cookies de sesión seguras · `.env`, `vendor/` y el código fuera de la
carpeta pública · exportación a Excel protegida contra fórmulas · el 404 no revela si un código existe.
