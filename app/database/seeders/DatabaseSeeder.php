<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario único y compartido del panel (Daniela y Miguel Ángel).
        // Credenciales desde el .env; nunca van en el código.
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@miguelydaniela.site')],
            [
                'name' => 'Novios',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'cambiar-esta-clave')),
            ],
        );

        // Invitados de muestra solo en desarrollo local.
        if (app()->isLocal() && Guest::count() === 0) {
            Guest::factory()->count(5)->create();
        }
    }
}
