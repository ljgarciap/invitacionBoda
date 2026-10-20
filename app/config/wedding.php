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
        'address' => env('WEDDING_VENUE_ADDRESS', 'Cl. 70 #33-376, Bucaramanga'),
    ],

    // Mensaje de WhatsApp para enviar cada invitación. Variables: {nombre} y {link}.
    'whatsapp_message' => env('WEDDING_WHATSAPP_MESSAGE', 'Hola {nombre}, tenemos algo muy especial para ti: nuestra invitación de boda. Ábrela aquí: {link}'),

    // Tema visual de la invitación: 'clasico' (nacarado y dorado) o 'botanico' (papel verde).
    'themes' => ['clasico', 'botanico'],
    'theme' => env('WEDDING_THEME', 'clasico'),

    // Caracteres permitidos en el código de la URL (sin 0/o, 1/l/i para evitar confusiones).
    'code_alphabet' => 'abcdefghjkmnpqrstuvwxyz23456789',
    'code_length' => 8,

];
