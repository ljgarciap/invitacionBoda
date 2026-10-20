<?php

return [

    /*
    | Datos del evento. Se leen del .env para ajustarlos sin tocar código.
    */
    'couple' => env('WEDDING_COUPLE', 'Miguel Ángel & Daniela'),

    'starts_at' => env('WEDDING_STARTS_AT', '2026-12-19 16:00:00'),

    // Fecha límite para confirmar (se incluye todo ese día).
    'rsvp_deadline' => env('WEDDING_RSVP_DEADLINE', '2026-12-04 23:59:59'),

    'venue' => [
        'name' => env('WEDDING_VENUE_NAME', 'Clásicos Forever - Salón Puente'),
        'address' => env('WEDDING_VENUE_ADDRESS', 'Cl. 70 #33-376, Bucaramanga, Santander'),
        // Ubicación exacta del lugar en Google Maps (la búsqueda por dirección no apuntaba bien).
        'lat' => env('WEDDING_VENUE_LAT', '7.106601'),
        'lng' => env('WEDDING_VENUE_LNG', '-73.102264'),
        'maps_url' => env('WEDDING_VENUE_MAPS_URL', 'https://www.google.com/maps/place/Cl%C3%A1sicos+Forever/@7.106601,-73.1048389,17z/data=!4m6!3m5!1s0x8e683feef5fee797:0xf37f42701079581!8m2!3d7.106601!4d-73.102264'),
    ],

    // Mensaje de WhatsApp para enviar cada invitación. Variables: {nombre} y {link}.
    'whatsapp_message' => env('WEDDING_WHATSAPP_MESSAGE', 'Hola {nombre}, tenemos algo muy especial para ti: nuestra invitación de boda. Ábrela aquí: {link}'),

    // Tema visual: 'clasico' (nacarado y dorado), 'botanico' (papel verde) o 'verde'
    // (la animación y estructura del clásico con los colores del botánico).
    'themes' => ['clasico', 'botanico', 'verde'],
    'theme' => env('WEDDING_THEME', 'verde'),

    // Caracteres permitidos en el código de la URL (sin 0/o, 1/l/i para evitar confusiones).
    'code_alphabet' => 'abcdefghjkmnpqrstuvwxyz23456789',
    'code_length' => 8,

];
