<?php

namespace Tests\Feature;

use App\Enums\GuestStatus;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_la_invitacion_con_nombre_y_cupos_y_registra_apertura(): void
    {
        $guest = Guest::factory()->create(['name' => 'Familia Pérez', 'seats' => 3]);

        $response = $this->get("/i/{$guest->code}");

        $response->assertOk()
            ->assertSee('Familia Pérez')
            ->assertSee('Tienes <b>3</b> cupos', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertNotNull($guest->fresh()->first_opened_at);
    }

    public function test_el_tema_por_defecto_es_clasico_y_se_puede_previsualizar_el_botanico(): void
    {
        $guest = Guest::factory()->create();

        $this->get("/i/{$guest->code}")->assertSee('data-theme="clasico"', false);
        $this->get("/i/{$guest->code}?tema=botanico")->assertSee('data-theme="botanico"', false);
    }

    public function test_un_tema_desconocido_cae_al_por_defecto(): void
    {
        $guest = Guest::factory()->create();

        $this->get("/i/{$guest->code}?tema=<script>")
            ->assertSee('data-theme="clasico"', false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_un_cupo_se_escribe_en_singular(): void
    {
        $guest = Guest::factory()->create(['seats' => 1]);

        $this->get("/i/{$guest->code}")->assertSee('Tienes <b>1</b> cupo reservado', false);
    }

    public function test_codigo_invalido_da_404_sin_revelar_datos(): void
    {
        Guest::factory()->create(['name' => 'Familia Secreta']);

        $this->get('/i/codigoinexistente')
            ->assertNotFound()
            ->assertSee('Invitación no encontrada')
            ->assertDontSee('Familia Secreta');
    }

    public function test_la_raiz_no_revela_nada(): void
    {
        $this->get('/')->assertNotFound();
    }

    public function test_acepta_y_guarda_la_respuesta(): void
    {
        $guest = Guest::factory()->create(['seats' => 3]);

        $this->postJson("/i/{$guest->code}/rsvp", [
            'status' => 'accepted',
            'attending' => 2,
            'names' => ['Ana', 'Luis'],
            'diet' => 'vegetariano',
            'message' => '¡Felicidades!',
        ])->assertOk()->assertJson(['status' => 'accepted', 'attending' => 2]);

        $guest = $guest->fresh();
        $this->assertSame(GuestStatus::Accepted, $guest->status);
        $this->assertSame(['Ana', 'Luis'], $guest->attendee_names);
    }

    public function test_rechaza_y_guarda_la_respuesta(): void
    {
        $guest = Guest::factory()->create();

        $this->postJson("/i/{$guest->code}/rsvp", ['status' => 'declined'])
            ->assertOk()->assertJson(['status' => 'declined']);

        $this->assertSame(GuestStatus::Declined, $guest->fresh()->status);
    }

    public function test_no_permite_mas_asistentes_que_cupos(): void
    {
        $guest = Guest::factory()->create(['seats' => 2]);

        $this->postJson("/i/{$guest->code}/rsvp", [
            'status' => 'accepted',
            'attending' => 3,
            'names' => ['A', 'B', 'C'],
        ])->assertStatus(422);

        $this->assertSame(GuestStatus::Pending, $guest->fresh()->status);
    }

    public function test_exige_nombres_al_aceptar(): void
    {
        $guest = Guest::factory()->create(['seats' => 2]);

        $this->postJson("/i/{$guest->code}/rsvp", ['status' => 'accepted', 'attending' => 2])
            ->assertStatus(422)->assertJsonValidationErrors(['names']);
    }

    public function test_los_nombres_deben_coincidir_con_el_numero_de_asistentes(): void
    {
        $guest = Guest::factory()->create(['seats' => 3]);

        $this->postJson("/i/{$guest->code}/rsvp", [
            'status' => 'accepted', 'attending' => 2, 'names' => ['Solo uno'],
        ])->assertStatus(422);
    }

    public function test_rechaza_estados_invalidos(): void
    {
        $guest = Guest::factory()->create();

        $this->postJson("/i/{$guest->code}/rsvp", ['status' => 'pending'])
            ->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    public function test_bloquea_la_respuesta_despues_del_plazo(): void
    {
        $guest = Guest::factory()->create();
        $this->travelTo('2026-12-05 09:00:00');

        $this->postJson("/i/{$guest->code}/rsvp", ['status' => 'declined'])->assertStatus(422);
        $this->assertSame(GuestStatus::Pending, $guest->fresh()->status);
    }

    public function test_la_vista_marca_el_plazo_cerrado(): void
    {
        $guest = Guest::factory()->create();
        $this->travelTo('2026-12-05 09:00:00');

        $this->get("/i/{$guest->code}")->assertOk()->assertSee('"open":false', false);
    }

    public function test_el_honeypot_ignora_el_envio_sin_guardar(): void
    {
        $guest = Guest::factory()->create(['seats' => 2]);

        $this->postJson("/i/{$guest->code}/rsvp", [
            'status' => 'accepted', 'attending' => 1, 'names' => ['Bot'], 'website' => 'http://spam.example',
        ])->assertOk();

        $this->assertSame(GuestStatus::Pending, $guest->fresh()->status);
    }

    public function test_la_vista_trae_la_respuesta_ya_guardada(): void
    {
        $guest = Guest::factory()->create(['seats' => 2]);
        $guest->accept(2, ['Ana', 'Luis']);

        $this->get("/i/{$guest->code}")
            ->assertOk()
            ->assertSee('"status":"accepted"', false)
            ->assertSee('"attending":2', false);
    }

    public function test_limita_los_intentos_de_rsvp(): void
    {
        $guest = Guest::factory()->create();

        foreach (range(1, 20) as $_) {
            $this->postJson("/i/{$guest->code}/rsvp", ['status' => 'declined']);
        }

        $this->postJson("/i/{$guest->code}/rsvp", ['status' => 'declined'])->assertStatus(429);
    }
}
