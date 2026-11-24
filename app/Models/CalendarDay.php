<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CalendarDayFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $day
 * @property CarbonImmutable $available_at
 * @property string $challenge_type
 * @property array|null $challenge_data
 * @property string|null $gift_title
 * @property string|null $gift_message
 * @property string|null $gift_image_path
 * @property string|null $gift_link_url
 * @property string|null $gift_link_label
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable([
    'day',
    'available_at',
    'challenge_type',
    'challenge_data',
    'gift_title',
    'gift_message',
    'gift_image_path',
    'gift_link_url',
    'gift_link_label',
])]
class CalendarDay extends Model
{
    /** @use HasFactory<CalendarDayFactory> */
    use HasFactory;

    public const string CHALLENGE_RIDDLE = 'riddle';

    public const string CHALLENGE_QCM = 'qcm';

    public const string CHALLENGE_GAME = 'game';

    protected function casts(): array
    {
        return [
            'day' => 'integer',
            'available_at' => 'date',
            'challenge_data' => 'array',
        ];
    }

    /**
     * The users who solved this day's challenge.
     *
     * @return BelongsToMany<User, $this>
     */
    public function solvers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'calendar_day_user')
            ->withPivot('solved_at');
    }

    public function isAvailableOn(CarbonImmutable $date): bool
    {
        // Testing shortcut: every door opens when the master switch is on.
        if ((bool) config('calendar.unlock_all')) {
            return true;
        }

        return $date->startOfDay()->greaterThanOrEqualTo($this->available_at);
    }
}
