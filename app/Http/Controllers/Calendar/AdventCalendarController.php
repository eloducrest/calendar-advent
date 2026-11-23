<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\CalendarDay;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdventCalendarController extends Controller
{
    /**
     * Show the advent calendar with the state of each door for the current user.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user();
        $today = now()->startOfDay();

        $days = CalendarDay::query()
            ->orderBy('day')
            ->with(['solvers' => fn ($query) => $query->whereKey($user->getKey())])
            ->get()
            ->map(fn (CalendarDay $day): array => $this->presentDay($day, $user, $today));

        return Inertia::render('calendar/Index', [
            'days' => $days,
            'today' => $today->toDateString(),
        ]);
    }

    /**
     * Check the challenge answer for a day and unlock the gift when correct.
     */
    public function solve(Request $request, CalendarDay $day): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->solvers()->whereKey($day->getKey())->exists()) {
            return back();
        }

        if (! $day->isAvailableOn(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'challenge' => __('Patience ! Cette case s\'ouvrira le :date.', [
                    'date' => $day->available_at->isoFormat('D MMMM YYYY'),
                ]),
            ]);
        }

        $this->validateAnswer($request, $day);

        $user->solvers()->attach($day->getKey(), ['solved_at' => now()]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bravo ! Ton cadeau est débloqué 🎁')]);

        return back();
    }

    /**
     * Present a calendar day for the front-end, hiding the gift until it is solved.
     *
     * @return array<string, mixed>
     */
    private function presentDay(CalendarDay $day, User $user, CarbonImmutable $today): array
    {
        $isAvailable = $day->isAvailableOn($today);
        $solvedPivot = $day->solvers->first()?->pivot;
        $solved = $solvedPivot !== null;
        $data = $day->challenge_data ?? [];

        return [
            'id' => $day->id,
            'day' => $day->day,
            'status' => $this->status($isAvailable, $solved),
            'available_at' => $day->available_at->toDateString(),
            'challenge_type' => $day->challenge_type,
            // The challenge statement is public; only the answers stay on the server.
            'challenge_question' => in_array($day->challenge_type, [CalendarDay::CHALLENGE_RIDDLE, CalendarDay::CHALLENGE_QCM], true)
                ? (string) ($data['question'] ?? '')
                : null,
            'qcm_options' => $day->challenge_type === CalendarDay::CHALLENGE_QCM
                ? array_values((array) ($data['options'] ?? []))
                : null,
            'game_title' => $day->challenge_type === CalendarDay::CHALLENGE_GAME
                ? (string) ($data['title'] ?? '')
                : null,
            'game_instructions' => $day->challenge_type === CalendarDay::CHALLENGE_GAME
                ? (string) ($data['instructions'] ?? '')
                : null,
            'game_symbols' => $day->challenge_type === CalendarDay::CHALLENGE_GAME
                ? array_values((array) ($data['symbols'] ?? []))
                : null,
            'has_gift' => $day->gift_title !== null
                || $day->gift_message !== null
                || $day->gift_image_path !== null
                || $day->gift_link_url !== null,
            'is_today' => $today->day === $day->day && $today->month === 12,
            'gift' => $this->presentGift($day, $solved),
        ];
    }

    private function status(bool $isAvailable, bool $solved): string
    {
        if ($solved) {
            return 'solved';
        }

        return $isAvailable ? 'available' : 'locked';
    }

    /**
     * The gift content is only revealed once the challenge is solved.
     *
     * @return array<string, mixed>|null
     */
    private function presentGift(CalendarDay $day, bool $solved): ?array
    {
        if (! $solved) {
            return null;
        }

        return [
            'title' => $day->gift_title,
            'message' => $day->gift_message,
            'image_url' => $day->gift_image_path !== null
                ? Storage::disk('public')->url($day->gift_image_path)
                : null,
            'link_url' => $day->gift_link_url,
            'link_label' => $day->gift_link_label,
        ];
    }

    /**
     * Validate the challenge answer depending on the challenge type.
     *
     * @throws ValidationException
     */
    private function validateAnswer(Request $request, CalendarDay $day): void
    {
        $data = $day->challenge_data ?? [];

        if ($day->challenge_type === CalendarDay::CHALLENGE_QCM) {
            $request->validate([
                'option' => ['required', 'integer', 'between:0,3'],
            ]);

            if ((int) $request->integer('option') !== (int) ($data['correct_index'] ?? -1)) {
                throw ValidationException::withMessages([
                    'challenge' => __('Ce n\'est pas la bonne réponse... Réessaie !'),
                ]);
            }

            return;
        }

        if ($day->challenge_type === CalendarDay::CHALLENGE_GAME) {
            $request->validate([
                'completed' => ['required', 'boolean', 'accepted'],
            ]);

            return;
        }

        $request->validate([
            'answer' => ['required', 'string', 'max:255'],
        ]);

        $accepted = array_map(
            fn (string $accepted): string => $this->normalizeAnswer($accepted),
            (array) ($data['accepted_answers'] ?? []),
        );

        if (! in_array($this->normalizeAnswer($request->string('answer')), $accepted, true)) {
            throw ValidationException::withMessages([
                'challenge' => __('Hmm, ce n\'est pas ça... Indice : réfléchis à Noël !'),
            ]);
        }
    }

    /**
     * Normalize an answer so small differences (accents, case, punctuation)
     * do not block the player.
     */
    private function normalizeAnswer(string $answer): string
    {
        $normalized = mb_strtolower(trim($answer));
        $normalized = transliterator_transliterate('Any-Latin; Latin-ASCII', $normalized)
            ?? strtr($normalized, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ù' => 'u', 'ç' => 'c', 'ï' => 'i', 'ô' => 'o']);

        return (string) trim((string) preg_replace('/[^\p{L}\p{N}\s]/u', '', preg_replace('/\s+/', ' ', $normalized)));
    }
}
