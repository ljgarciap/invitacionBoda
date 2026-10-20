<?php

namespace App\Models;

use App\Enums\GuestStatus;
use App\Exceptions\RsvpException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'seats'];

    protected function casts(): array
    {
        return [
            'status' => GuestStatus::class,
            'attendee_names' => 'array',
            'first_opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            $guest->code ??= static::generateCode();
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = Str::of(config('wedding.code_alphabet'))
                ->split(1)
                ->random(config('wedding.code_length'))
                ->implode('');
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public static function rsvpIsOpen(?Carbon $now = null): bool
    {
        $deadline = Carbon::parse(config('wedding.rsvp_deadline'), config('app.timezone'));

        return ($now ?? now())->lessThanOrEqualTo($deadline);
    }

    public function markOpened(): void
    {
        $this->first_opened_at ??= now();
        $this->last_opened_at = now();
        $this->save();
    }

    /**
     * @param  list<string>  $names  un nombre por cada asistente
     */
    public function accept(int $attending, array $names, ?string $diet = null, ?string $message = null): void
    {
        $this->assertOpen();

        if ($attending < 1 || $attending > $this->seats) {
            throw RsvpException::tooManyAttendees($this->seats);
        }

        $names = array_values(array_filter(array_map('trim', $names), 'strlen'));
        if (count($names) !== $attending) {
            throw RsvpException::namesMismatch();
        }

        $this->forceFill([
            'status' => GuestStatus::Accepted,
            'attending' => $attending,
            'attendee_names' => $names,
            'diet' => $diet ?: null,
            'message' => $message ?: null,
            'responded_at' => now(),
        ])->save();
    }

    public function decline(?string $message = null): void
    {
        $this->assertOpen();

        $this->forceFill([
            'status' => GuestStatus::Declined,
            'attending' => 0,
            'attendee_names' => null,
            'diet' => null,
            'message' => $message ?: null,
            'responded_at' => now(),
        ])->save();
    }

    private function assertOpen(): void
    {
        if (! static::rsvpIsOpen()) {
            throw RsvpException::closed();
        }
    }
}
