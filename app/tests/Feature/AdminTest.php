<?php

namespace Tests\Feature;

use App\Enums\GuestStatus;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function login(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    // ---------- Acceso ----------

    public function test_el_panel_exige_login(): void
    {
        $guest = Guest::factory()->create();

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/export')->assertRedirect('/admin/login');
        $this->post('/admin/import')->assertRedirect('/admin/login');
        $this->get("/admin/guests/{$guest->code}/edit")->assertRedirect('/admin/login');
        $this->delete("/admin/guests/{$guest->code}")->assertRedirect('/admin/login');
        $this->assertModelExists($guest);
    }

    public function test_login_correcto_entra_al_panel(): void
    {
        $user = User::factory()->create(['password' => 'secreta-123']);

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'secreta-123'])
            ->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_incorrecto_no_revela_si_el_correo_existe(): void
    {
        $user = User::factory()->create(['password' => 'secreta-123']);

        $malaClave = $this->post('/admin/login', ['email' => $user->email, 'password' => 'otra']);
        $noExiste = $this->post('/admin/login', ['email' => 'nadie@example.com', 'password' => 'otra']);

        $malaClave->assertSessionHasErrors(['email' => 'Correo o clave incorrectos.']);
        $noExiste->assertSessionHasErrors(['email' => 'Correo o clave incorrectos.']);
        $this->assertGuest();
    }

    public function test_el_login_tiene_limite_de_intentos(): void
    {
        foreach (range(1, 5) as $_) {
            $this->post('/admin/login', ['email' => 'a@b.co', 'password' => 'x']);
        }

        $this->post('/admin/login', ['email' => 'a@b.co', 'password' => 'x'])->assertStatus(429);
    }

    public function test_logout_cierra_la_sesion(): void
    {
        $this->login();

        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    // ---------- Listado y estadísticas ----------

    public function test_muestra_estadisticas_y_filtra(): void
    {
        $this->login();
        $a = Guest::factory()->create(['name' => 'Familia Alfa', 'seats' => 3]);
        $b = Guest::factory()->create(['name' => 'Familia Beta', 'seats' => 2]);
        Guest::factory()->create(['name' => 'Familia Gamma', 'seats' => 2]);
        $a->accept(2, ['Ana', 'Luis']);
        $b->decline();

        $this->get('/admin')->assertOk()
            ->assertSeeInOrder(['<b>2</b><span>personas confirmadas', '<b>1</b><span>invitaciones que aceptaron'], false)
            ->assertSee('Familia Alfa')->assertSee('Familia Gamma');

        $this->get('/admin?estado=accepted')->assertSee('Familia Alfa')->assertDontSee('Familia Beta');
        $this->get('/admin?estado=pending')->assertSee('Familia Gamma')->assertDontSee('Familia Alfa');
        $this->get('/admin?q=beta')->assertSee('Familia Beta')->assertDontSee('Familia Alfa');
    }

    public function test_la_busqueda_no_interpreta_comodines(): void
    {
        $this->login();
        Guest::factory()->create(['name' => 'Familia Alfa']);

        $this->get('/admin?q=%25')->assertDontSee('Familia Alfa');
    }

    public function test_cuenta_los_que_no_han_abierto_el_link(): void
    {
        $this->login();
        $vista = Guest::factory()->create();
        Guest::factory()->create();
        $vista->markOpened();

        $this->get('/admin?estado=unopened')->assertOk()->assertDontSee($vista->name);
    }

    // ---------- CRUD ----------

    public function test_crea_una_invitacion_con_codigo(): void
    {
        $this->login();

        $this->post('/admin/guests', ['name' => 'Familia Pérez', 'seats' => 3, 'phone' => '3001234567'])
            ->assertRedirect('/admin');

        $guest = Guest::firstWhere('name', 'Familia Pérez');
        $this->assertSame(3, $guest->seats);
        $this->assertMatchesRegularExpression('/^[a-hj-km-np-z2-9]{8}$/', $guest->code);
    }

    public function test_valida_los_datos_al_crear(): void
    {
        $this->login();

        $this->post('/admin/guests', ['name' => '', 'seats' => 0])
            ->assertSessionHasErrors(['name', 'seats']);
        $this->post('/admin/guests', ['name' => 'X', 'seats' => 99])->assertSessionHasErrors('seats');
        $this->assertSame(0, Guest::count());
    }

    public function test_edita_nombre_y_cupos(): void
    {
        $this->login();
        $guest = Guest::factory()->create(['seats' => 2]);

        $this->put("/admin/guests/{$guest->code}", ['name' => 'Nuevo Nombre', 'seats' => 4])
            ->assertRedirect('/admin');

        $this->assertSame('Nuevo Nombre', $guest->fresh()->name);
        $this->assertSame(4, $guest->fresh()->seats);
    }

    public function test_el_codigo_no_cambia_al_editar(): void
    {
        $this->login();
        $guest = Guest::factory()->create();
        $code = $guest->code;

        $this->put("/admin/guests/{$code}", ['name' => 'Otro', 'seats' => 1, 'code' => 'hackeado1']);

        $this->assertSame($code, $guest->fresh()->code);
    }

    public function test_no_baja_los_cupos_por_debajo_de_lo_confirmado(): void
    {
        $this->login();
        $guest = Guest::factory()->create(['seats' => 4]);
        $guest->accept(3, ['A', 'B', 'C']);

        $this->put("/admin/guests/{$guest->code}", ['name' => $guest->name, 'seats' => 2])
            ->assertSessionHasErrors('seats');
        $this->assertSame(4, $guest->fresh()->seats);
    }

    public function test_reinicia_una_respuesta(): void
    {
        $this->login();
        $guest = Guest::factory()->create(['seats' => 2]);
        $guest->accept(2, ['Ana', 'Luis'], 'sin gluten', 'hola');

        $this->post("/admin/guests/{$guest->code}/reset")->assertRedirect();

        $guest = $guest->fresh();
        $this->assertSame(GuestStatus::Pending, $guest->status);
        $this->assertNull($guest->attending);
        $this->assertNull($guest->message);
    }

    public function test_elimina_una_invitacion_y_su_link_deja_de_funcionar(): void
    {
        $this->login();
        $guest = Guest::factory()->create();
        $code = $guest->code;

        $this->delete("/admin/guests/{$code}")->assertRedirect('/admin');

        $this->assertModelMissing($guest);
        $this->get("/i/{$code}")->assertNotFound();
    }

    // ---------- WhatsApp ----------

    public function test_arma_el_enlace_de_whatsapp_con_prefijo_de_colombia_y_mensaje(): void
    {
        $guest = Guest::factory()->create(['name' => 'Familia Pérez', 'phone' => '300 123 4567']);

        $url = $guest->whatsappUrl();

        $this->assertStringStartsWith('https://wa.me/573001234567?text=', $url);
        $text = urldecode(substr($url, strpos($url, 'text=') + 5));
        $this->assertSame("Hola Familia Pérez, tenemos algo especial para ti 💌 {$guest->invitationUrl()}", $text);
    }

    public function test_sin_telefono_deja_elegir_el_contacto(): void
    {
        $guest = Guest::factory()->create(['phone' => null]);

        $this->assertStringStartsWith('https://wa.me/?text=', $guest->whatsappUrl());
    }

    public function test_respeta_un_telefono_internacional(): void
    {
        $guest = Guest::factory()->create(['phone' => '+1 (305) 555-0100']);

        $this->assertSame('13055550100', $guest->whatsappPhone());
    }

    // ---------- Importar / exportar ----------

    public function test_importa_csv_con_punto_y_coma_y_encabezado(): void
    {
        $this->login();
        $csv = "nombre;cupos;telefono\nFamilia Pérez;3;3001234567\nJuan Gómez;1;\n";

        $this->post('/admin/import', ['file' => UploadedFile::fake()->createWithContent('i.csv', $csv)])
            ->assertRedirect('/admin');

        $this->assertSame(2, Guest::count());
        $this->assertSame(3, Guest::firstWhere('name', 'Familia Pérez')->seats);
    }

    public function test_importa_csv_con_comas_sin_encabezado_y_con_bom(): void
    {
        $this->login();
        $csv = "\xEF\xBB\xBFAna Ruiz,2,3115550000\nLuis Mora,1\n";

        $this->post('/admin/import', ['file' => UploadedFile::fake()->createWithContent('i.csv', $csv)]);

        $this->assertSame(2, Guest::count());
        $this->assertSame('Ana Ruiz', Guest::orderBy('id')->first()->name);
    }

    public function test_importa_las_filas_validas_y_reporta_las_invalidas(): void
    {
        $this->login();
        $csv = "Familia Buena;2;300\n;2;300\nFamilia Cero;0;300\nFamilia Texto;abc;300\n";

        $this->post('/admin/import', ['file' => UploadedFile::fake()->createWithContent('i.csv', $csv)])
            ->assertRedirect('/admin')
            ->assertSessionHas('import_errors', fn ($e) => count($e) === 3);

        $this->assertSame(1, Guest::count());
    }

    public function test_rechaza_archivos_que_no_son_csv(): void
    {
        $this->login();

        $this->post('/admin/import', ['file' => UploadedFile::fake()->create('virus.exe', 10)])
            ->assertSessionHasErrors('file');
        $this->assertSame(0, Guest::count());
    }

    public function test_exporta_csv_con_las_respuestas(): void
    {
        $this->login();
        $guest = Guest::factory()->create(['name' => 'Familia Pérez', 'seats' => 3]);
        $guest->accept(2, ['Ana', 'Luis'], 'vegetariano', 'Felicidades');

        $response = $this->get('/admin/export');

        $response->assertOk()->assertDownload();
        $body = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertStringContainsString('"Familia Pérez";3;', $body);
        $this->assertStringContainsString('Confirmó;2;"Ana, Luis";vegetariano;Felicidades', $body);
        $this->assertStringContainsString($guest->invitationUrl(), $body);
    }

    public function test_la_exportacion_neutraliza_formulas(): void
    {
        $this->login();
        $guest = Guest::factory()->create(['seats' => 1]);
        $guest->accept(1, ['Ana'], null, '=HYPERLINK("http://malo.example")');

        $body = $this->get('/admin/export')->streamedContent();

        $this->assertStringNotContainsString(';=HYPERLINK', $body);
        $this->assertStringContainsString("'=HYPERLINK", $body);
    }
}
