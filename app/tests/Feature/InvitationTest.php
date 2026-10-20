<?php

namespace Tests\Feature;

use App\Enums\GuestStatus;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_el_tema_por_defecto_es_verde_y_se_pueden_previsualizar_los_otros(): void
    {
        $guest = Guest::factory()->create();

        $this->get("/i/{$guest->code}")->assertSee('data-theme="verde"', false);
        $this->get("/i/{$guest->code}?tema=clasico")->assertSee('data-theme="clasico"', false);
        $this->get("/i/{$guest->code}?tema=botanico")->assertSee('data-theme="botanico"', false);
    }

    public function test_un_tema_desconocido_cae_al_por_defecto(): void
    {
        $guest = Guest::factory()->create();

        $this->get("/i/{$guest->code}?tema=<script>")
            ->assertSee('data-theme="verde"', false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_el_mapa_apunta_al_lugar_exacto_y_no_a_una_busqueda_por_direccion(): void
    {
        $guest = Guest::factory()->create();

        $this->get("/i/{$guest->code}")
            ->assertSee('Cl. 70 #33-376, Bucaramanga, Santander')
            ->assertSee('Cl%C3%A1sicos+Forever/@7.106601', false)
            ->assertSee('maps?q=Cl%C3%A1sicos+Forever', false)
            ->assertSee('&ll=7.106601,-73.102264&z=17&output=embed', false)
            ->assertSee('waze.com/ul?ll=7.106601,-73.102264', false);
    }

    public function test_muestra_el_itinerario_actualizado_el_versiculo_y_la_lluvia_de_sobres(): void
    {
        $guest = Guest::factory()->create();

        $this->get("/i/{$guest->code}")
            ->assertSeeInOrder(['4:00 PM', 'Ceremonia', '6:30 PM', 'Bienvenida &amp; cóctel', '7:10 PM', '7:20 PM', '8:15 PM', 'Momentos especiales', '9:30 PM', 'Cena', '11:00 PM', 'Hora loca', '1:00 AM', 'Fin de nuestra celebración'], false)
            ->assertSee('Colosenses 3:14')
            ->assertSee('Que Dios sea siempre el centro de nuestra historia.')
            ->assertSee('Lluvia de sobres');
    }

    public function test_incluye_la_vista_previa_para_whatsapp(): void
    {
        $guest = Guest::factory()->create();

        $this->get("/i/{$guest->code}")
            ->assertSee('property="og:image" content="'.asset('invitacion/img/og.jpg').'"', false)
            ->assertSee('property="og:image:width" content="1200"', false)
            ->assertSee('property="og:type" content="website"', false)
            ->assertSee('property="og:url" content="'.url("/i/{$guest->code}").'"', false);
        $this->assertFileExists(public_path('invitacion/img/og.jpg'));
        $this->assertLessThan(300 * 1024, filesize(public_path('invitacion/img/og.jpg')));
    }

    public function test_la_vista_previa_no_incluye_el_nombre_del_invitado(): void
    {
        $guest = Guest::factory()->create(['name' => 'Familia Secreta']);

        $html = $this->get("/i/{$guest->code}")->getContent();
        preg_match_all('/<meta property="og:[^>]+>/', $html, $m);

        $this->assertStringNotContainsString('Secreta', implode('', $m[0]));
    }

    #[DataProvider('robots')]
    public function test_los_robots_de_vista_previa_no_cuentan_como_apertura(string $userAgent): void
    {
        $guest = Guest::factory()->create();

        $this->withHeader('User-Agent', $userAgent)->get("/i/{$guest->code}")->assertOk();

        $this->assertNull($guest->fresh()->first_opened_at);
    }

    public static function robots(): array
    {
        return [
            'whatsapp' => ['WhatsApp/2.23.20.0 A'],
            'facebook' => ['facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'],
            'telegram' => ['TelegramBot (like TwitterBot)'],
            'google' => ['Mozilla/5.0 (compatible; Googlebot/2.1)'],
        ];
    }

    public function test_un_navegador_real_si_cuenta_como_apertura(): void
    {
        $guest = Guest::factory()->create();
        $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

        $this->withHeader('User-Agent', $ua)->get("/i/{$guest->code}")->assertOk();

        $this->assertNotNull($guest->fresh()->first_opened_at);
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
