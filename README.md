# Invitación de boda · Miguel Ángel & Daniela

Invitación interactiva con links personalizados por invitado, cuenta atrás, ubicación, RSVP con cupos y
panel de administración. Laravel 13 · PHP 8.4 · MariaDB/MySQL.

- 📖 [Manual de uso para los novios](docs/manual-de-uso.md)
- 🚀 [Guía de despliegue en Hostinger](docs/despliegue-hostinger.md)

## Desarrollo local (Docker)

```bash
docker compose up -d --build
docker compose exec app composer install        # solo la primera vez
cp app/.env.example app/.env                     # y ajustar DB_* (ver docker-compose.yml)
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
```

- App: <http://localhost:8000> (la raíz devuelve 404 a propósito; usa `/i/{codigo}` y `/admin`).
- Variables de `.env` para desarrollo: `DB_CONNECTION=mariadb`, `DB_HOST=db`, `DB_DATABASE/USERNAME/PASSWORD=invitacion`.
- Usuario del panel local: `ADMIN_EMAIL` / `ADMIN_PASSWORD` en el `.env` (el seeder los usa).
- Comparar diseños: `/i/{codigo}?tema=clasico` o `?tema=botanico`.

## Estructura

| Ruta | Contenido |
|---|---|
| `app/` | Proyecto Laravel |
| `app/public/invitacion/` | CSS, JS, imágenes y música de la invitación |
| `app/resources/views/` | `invitation.blade.php` (invitado) y `admin/` (panel) |
| `app/config/wedding.php` | Fecha, plazo, lugar, tema y mensaje de WhatsApp |
| `prototipo/` | Prototipo visual estático original (sin backend) |
| `docs/` | Manual de uso y guía de despliegue |
