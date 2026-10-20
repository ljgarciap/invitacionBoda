<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetAdminPassword extends Command
{
    protected $signature = 'admin:password {email=admin@miguelydaniela.site : Correo del usuario del panel}';

    protected $description = 'Crea o actualiza la clave del usuario único del panel /admin';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('El correo no es válido.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('Nueva clave (mínimo 12 caracteres)');

        if (mb_strlen($password) < 12) {
            $this->error('La clave debe tener al menos 12 caracteres.');

            return self::FAILURE;
        }

        if ($password !== (string) $this->secret('Repite la clave')) {
            $this->error('Las claves no coinciden.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            ['name' => 'Novios', 'password' => Hash::make($password)],
        );

        $this->info($user->wasRecentlyCreated ? "Usuario {$email} creado." : "Clave de {$email} actualizada.");

        return self::SUCCESS;
    }
}
