<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GuestStatus;
use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    private const MAX_IMPORT_ROWS = 500;

    public function index(Request $request): View
    {
        $status = $request->query('estado');
        $q = trim((string) $request->query('q'));

        $guests = Guest::query()
            ->when($status === 'unopened', fn ($query) => $query->whereNull('first_opened_at'))
            ->when(in_array($status, array_column(GuestStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status))
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.addcslashes($q, '%_\\').'%'))
            ->orderBy('name')
            ->get();

        return view('admin.index', [
            'guests' => $guests,
            'stats' => $this->stats(),
            'status' => $status,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.form', ['guest' => new Guest(['seats' => 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $guest = Guest::create($this->validated($request));

        return redirect()->route('admin.index')->with('ok', "Invitación creada para {$guest->name}.");
    }

    public function edit(Guest $guest): View
    {
        return view('admin.form', ['guest' => $guest]);
    }

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $data = $this->validated($request);

        // Si ya confirmó, no se pueden reducir los cupos por debajo de lo confirmado.
        if ($guest->attending && $data['seats'] < $guest->attending) {
            return back()->withInput()->withErrors([
                'seats' => "Ya confirmó {$guest->attending} persona(s); reinicia su respuesta antes de bajar los cupos.",
            ]);
        }

        $guest->update($data);

        return redirect()->route('admin.index')->with('ok', "Cambios guardados para {$guest->name}.");
    }

    public function reset(Guest $guest): RedirectResponse
    {
        $guest->resetResponse();

        return redirect()->route('admin.guests.edit', $guest)->with('ok', 'Respuesta reiniciada: vuelve a quedar pendiente.');
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        $guest->delete();

        return redirect()->route('admin.index')->with('ok', "Se eliminó la invitación de {$guest->name}.");
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:1024']]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $first = fgets($handle);
        rewind($handle);
        $delimiter = substr_count((string) $first, ';') > substr_count((string) $first, ',') ? ';' : ',';

        $rows = [];
        $errors = [];
        $line = 0;

        while (($cols = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            $cols = array_map(fn ($c) => trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $c)), $cols);

            if ($cols === [''] || $cols === []) {
                continue;
            }
            // Encabezado opcional: "nombre;cupos;telefono"
            if ($line === 1 && stripos($cols[0], 'nombre') !== false) {
                continue;
            }

            $validator = Validator::make(
                ['name' => $cols[0] ?? '', 'seats' => $cols[1] ?? '1', 'phone' => $cols[2] ?? null],
                [
                    'name' => ['required', 'string', 'max:255'],
                    'seats' => ['required', 'integer', 'min:1', 'max:20'],
                    'phone' => ['nullable', 'string', 'max:30'],
                ],
            );

            if ($validator->fails()) {
                $errors[] = "Línea {$line}: ".$validator->errors()->first();

                continue;
            }

            $rows[] = $validator->validated();
        }
        fclose($handle);

        if (count($rows) > self::MAX_IMPORT_ROWS) {
            return back()->withErrors(['file' => 'El archivo supera las '.self::MAX_IMPORT_ROWS.' filas.']);
        }

        DB::transaction(fn () => collect($rows)->each(fn ($row) => Guest::create($row)));

        $msg = count($rows).' invitación(es) importada(s).';

        return redirect()->route('admin.index')
            ->with('ok', $msg)
            ->with('import_errors', $errors);
    }

    public function export(): StreamedResponse
    {
        $headers = ['Nombre', 'Cupos', 'Teléfono', 'Estado', 'Asistentes', 'Nombres', 'Restricciones', 'Mensaje', 'Link', 'Abierta', 'Respondió'];

        return response()->streamDownload(function () use ($headers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel lea bien los acentos
            fputcsv($out, $headers, ';');

            Guest::orderBy('name')->each(function (Guest $g) use ($out) {
                fputcsv($out, array_map([$this, 'safeCell'], [
                    $g->name,
                    $g->seats,
                    $g->phone,
                    $g->status->label(),
                    $g->attending,
                    implode(', ', $g->attendee_names ?? []),
                    $g->diet,
                    $g->message,
                    $g->invitationUrl(),
                    $g->first_opened_at?->format('Y-m-d H:i'),
                    $g->responded_at?->format('Y-m-d H:i'),
                ]), ';');
            });

            fclose($out);
        }, 'invitados-boda-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Evita que Excel interprete texto del invitado como fórmula (=, +, -, @). */
    private function safeCell(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /** @return array{name: string, seats: int, phone: ?string} */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'seats' => ['required', 'integer', 'min:1', 'max:20'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
    }

    /** @return array<string, int> */
    private function stats(): array
    {
        $all = Guest::query();

        return [
            'invitations' => (clone $all)->count(),
            'seats' => (int) (clone $all)->sum('seats'),
            'accepted' => (clone $all)->where('status', GuestStatus::Accepted)->count(),
            'attending' => (int) (clone $all)->where('status', GuestStatus::Accepted)->sum('attending'),
            'declined' => (clone $all)->where('status', GuestStatus::Declined)->count(),
            'pending' => (clone $all)->where('status', GuestStatus::Pending)->count(),
            'unopened' => (clone $all)->whereNull('first_opened_at')->count(),
        ];
    }
}
