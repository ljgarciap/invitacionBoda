<?php

namespace App\Http\Controllers;

use App\Enums\GuestStatus;
use App\Exceptions\RsvpException;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class InvitationController extends Controller
{
    public function show(Guest $guest): Response
    {
        $guest->markOpened();

        $tz = config('app.timezone');
        $starts = Carbon::parse(config('wedding.starts_at'), $tz);

        $state = [
            'name' => $guest->name,
            'seats' => $guest->seats,
            'status' => $guest->status->value,
            'attending' => $guest->attending,
            'names' => $guest->attendee_names ?? [],
            'diet' => $guest->diet,
            'message' => $guest->message,
            'open' => Guest::rsvpIsOpen(),
            'rsvpUrl' => route('invitation.rsvp', $guest),
            'startsAt' => $starts->toIso8601String(),
            'endsAt' => $starts->copy()->addHours(9)->toIso8601String(),
            'venue' => config('wedding.venue.name').', '.config('wedding.venue.address'),
        ];

        return response()
            ->view('invitation', ['guest' => $guest, 'state' => $state])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function rsvp(Request $request, Guest $guest): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,declined'],
            'attending' => ['required_if:status,accepted', 'nullable', 'integer', 'min:1', 'max:'.$guest->seats],
            'names' => ['required_if:status,accepted', 'nullable', 'array', 'max:'.$guest->seats],
            'names.*' => ['required', 'string', 'max:80'],
            'diet' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string'], // honeypot: un humano nunca lo llena
        ]);

        if (filled($data['website'] ?? null)) {
            return response()->json(['status' => GuestStatus::Pending->value]);
        }

        try {
            if ($data['status'] === 'accepted') {
                $guest->accept($data['attending'], $data['names'], $data['diet'] ?? null, $data['message'] ?? null);
            } else {
                $guest->decline($data['message'] ?? null);
            }
        } catch (RsvpException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => $guest->status->value,
            'attending' => $guest->attending,
        ]);
    }
}
