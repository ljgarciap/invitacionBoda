<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetAdminPasswordTest extends TestCase
{
    use RefreshDatabase;

    private const ASK = 'Nueva clave (mínimo 12 caracteres)';
    private const CONFIRM = 'Repite la clave';

    public function test_crea_el_usuario_si_no_existe(): void
    {
        $this->artisan('admin:password', ['email' => 'novios@miguelydaniela.site'])
            ->expectsQuestion(self::ASK, 'una-clave-larga-123')
            ->expectsQuestion(self::CONFIRM, 'una-clave-larga-123')
            ->expectsOutputToContain('creado')
            ->assertSuccessful();

        $user = User::firstWhere('email', 'novios@miguelydaniela.site');
        $this->assertTrue(Hash::check('una-clave-larga-123', $user->password));
    }

    public function test_actualiza_la_clave_sin_duplicar_el_usuario(): void
    {
        User::factory()->create(['email' => 'novios@miguelydaniela.site', 'password' => 'vieja-clave-123']);

        $this->artisan('admin:password', ['email' => 'novios@miguelydaniela.site'])
            ->expectsQuestion(self::ASK, 'otra-clave-larga-456')
            ->expectsQuestion(self::CONFIRM, 'otra-clave-larga-456')
            ->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertTrue(Hash::check('otra-clave-larga-456', User::first()->password));
    }

    public function test_rechaza_claves_cortas(): void
    {
        $this->artisan('admin:password')
            ->expectsQuestion(self::ASK, 'corta')
            ->assertFailed();

        $this->assertSame(0, User::count());
    }

    public function test_rechaza_si_la_confirmacion_no_coincide(): void
    {
        $this->artisan('admin:password')
            ->expectsQuestion(self::ASK, 'una-clave-larga-123')
            ->expectsQuestion(self::CONFIRM, 'una-clave-distinta-1')
            ->assertFailed();

        $this->assertSame(0, User::count());
    }

    public function test_rechaza_un_correo_invalido(): void
    {
        $this->artisan('admin:password', ['email' => 'no-es-correo'])->assertFailed();
    }
}
