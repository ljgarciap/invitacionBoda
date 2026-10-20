<?php

namespace Tests\Feature;

use App\Enums\GuestStatus;
use App\Exceptions\RsvpException;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTest extends TestCase
{
    use RefreshDatabase;

    public function test_genera_codigo_aleatorio_unico_al_crear(): void
    {
        $guests = Guest::factory()->count(30)->create();

        $codes = $guests->pluck('code');
        $this->assertCount(30, $codes->unique());
        $codes->each(fn ($c) => $this->assertMatchesRegularExpression('/^[a-hj-km-np-z2-9]{8}$/', $c));
    }

    public function test_nace_pendiente(): void
    {
        $guest = Guest::factory()->create(['seats' => 3]);

        $this->assertSame(GuestStatus::Pending, $guest->fresh()->status);
        $this->assertNull($guest->attending);
    }

    public function test_acepta_con_nombres_y_dentro_de_los_cupos(): void
    {
        $guest = Guest::factory()->create(['seats' => 3]);

        $guest->accept(2, ['Ana', ' Luis '], 'vegetariano', 'Felicidades');

        $guest = $guest->fresh();
        $this->assertSame(GuestStatus::Accepted, $guest->status);
        $this->assertSame(2, $guest->attending);
        $this->assertSame(['Ana', 'Luis'], $guest->attendee_names);
        $this->assertSame('vegetariano', $guest->diet);
        $this->assertNotNull($guest->responded_at);
    }

    public function test_no_acepta_mas_personas_que_cupos(): void
    {
        $guest = Guest::factory()->create(['seats' => 2]);

        $this->expectException(RsvpException::class);
        $guest->accept(3, ['A', 'B', 'C']);
    }

    public function test_exige_un_nombre_por_asistente(): void
    {
        $guest = Guest::factory()->create(['seats' => 3]);

        $this->expectException(RsvpException::class);
        $guest->accept(2, ['Solo uno']);
    }

    public function test_no_acepta_cero_asistentes(): void
    {
        $guest = Guest::factory()->create(['seats' => 3]);

        $this->expectException(RsvpException::class);
        $guest->accept(0, []);
    }

    public function test_rechaza_y_limpia_datos_de_asistencia(): void
    {
        $guest = Guest::factory()->create(['seats' => 3]);
        $guest->accept(2, ['Ana', 'Luis'], 'sin gluten');

        $guest->decline('No podemos, ¡mucho cariño!');

        $guest = $guest->fresh();
        $this->assertSame(GuestStatus::Declined, $guest->status);
        $this->assertSame(0, $guest->attending);
        $this->assertNull($guest->attendee_names);
        $this->assertNull($guest->diet);
    }

    public function test_puede_cambiar_de_opinion_antes_del_plazo(): void
    {
        $guest = Guest::factory()->create(['seats' => 2]);
        $guest->decline();

        $guest->accept(2, ['Ana', 'Luis']);

        $this->assertSame(GuestStatus::Accepted, $guest->fresh()->status);
    }

    public function test_bloquea_respuestas_despues_del_plazo(): void
    {
        $guest = Guest::factory()->create(['seats' => 2]);
        $this->travelTo('2026-12-05 08:00:00');

        $this->assertFalse(Guest::rsvpIsOpen());
        $this->expectException(RsvpException::class);
        $guest->decline();
    }

    public function test_el_plazo_incluye_todo_el_4_de_diciembre(): void
    {
        $this->travelTo('2026-12-04 23:30:00');
        $this->assertTrue(Guest::rsvpIsOpen());
    }

    public function test_registra_primera_y_ultima_apertura(): void
    {
        $guest = Guest::factory()->create();

        $this->travelTo('2026-11-01 10:00:00');
        $guest->markOpened();
        $first = $guest->fresh()->first_opened_at;

        $this->travelTo('2026-11-02 10:00:00');
        $guest->markOpened();

        $guest = $guest->fresh();
        $this->assertEquals($first, $guest->first_opened_at);
        $this->assertTrue($guest->last_opened_at->greaterThan($guest->first_opened_at));
    }

    public function test_la_ruta_usa_el_codigo(): void
    {
        $guest = Guest::factory()->create();

        $this->assertSame('code', $guest->getRouteKeyName());
        $this->assertSame($guest->code, $guest->getRouteKey());
    }
}
