<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Calendar\UpdateCalendarDayRequest;
use App\Models\CalendarDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CalendarDayController extends Controller
{
    /**
     * Show the edit screen listing all calendar days.
     */
    public function index(): Response
    {
        $days = CalendarDay::query()
            ->orderBy('day')
            ->get()
            ->map(fn (CalendarDay $day): array => [
                'id' => $day->id,
                'day' => $day->day,
                'available_at' => $day->available_at->toDateString(),
                'challenge_type' => $day->challenge_type,
                'challenge_summary' => $this->challengeSummary($day),
                'gift_title' => $day->gift_title,
                'gift_image_url' => $day->gift_image_path !== null
                    ? Storage::disk('public')->url($day->gift_image_path)
                    : null,
            ]);

        return Inertia::render('calendar/Edit', ['days' => $days]);
    }

    /**
     * Show the edit form for one calendar day.
     */
    public function edit(CalendarDay $day): Response
    {
        return Inertia::render('calendar/EditDay', ['day' => $this->presentForEdit($day)]);
    }

    /**
     * Update one calendar day's challenge and gift.
     */
    public function update(UpdateCalendarDayRequest $request, CalendarDay $day): RedirectResponse
    {
        $day->fill($request->safe()->only(['challenge_type', 'available_at']));

        if ($request->boolean('remove_gift_image') && $day->gift_image_path !== null) {
            Storage::disk('public')->delete($day->gift_image_path);
            $day->gift_image_path = null;
        }

        if ($request->file('gift_image') !== null) {
            if ($day->gift_image_path !== null) {
                Storage::disk('public')->delete($day->gift_image_path);
            }
            $day->gift_image_path = $request->file('gift_image')->store('gifts', 'public');
        }

        $day->challenge_data = $request->challengeData();
        $day->fill($request->safe()->only([
            'gift_title',
            'gift_message',
            'gift_link_url',
            'gift_link_label',
        ]));
        $day->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Case :day mise à jour ✨', ['day' => $day->day])]);

        return to_route('calendar.edit');
    }

    /**
     * Short label of the configured challenge, for the edit overview.
     */
    private function challengeSummary(CalendarDay $day): string
    {
        $data = $day->challenge_data ?? [];

        return match ($day->challenge_type) {
            CalendarDay::CHALLENGE_QCM => (string) ($data['question'] ?? ''),
            CalendarDay::CHALLENGE_GAME => $this->gameLabels()[(string) (($data['game'] ?? null) ?: 'memory')].' — '.(string) ($data['title'] ?? ''),
            default => (string) ($data['question'] ?? ''),
        };
    }

    /**
     * French labels of the mini-game variants, keyed by their stored name.
     *
     * @return array<string, string>
     */
    private function gameLabels(): array
    {
        return [
            'memory' => 'Memory',
            'simon' => 'Simon',
            'puzzle' => 'Taquin',
            'mole' => 'Frappe-Taupe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentForEdit(CalendarDay $day): array
    {
        $data = $day->challenge_data ?? [];

        return [
            'id' => $day->id,
            'day' => $day->day,
            'available_at' => $day->available_at->toDateString(),
            'challenge_type' => $day->challenge_type,
            'riddle_question' => $data['question'] ?? '',
            'riddle_answers' => implode("\n", (array) ($data['accepted_answers'] ?? [])),
            'qcm_question' => $data['question'] ?? '',
            'qcm_options' => array_pad((array) ($data['options'] ?? []), 3, ''),
            'qcm_correct_index' => $data['correct_index'] ?? 0,
            'game_title' => $data['title'] ?? '',
            'game_variant' => (string) (($data['game'] ?? null) ?: 'memory'),
            'game_instructions' => $data['instructions'] ?? '',
            'game_symbols' => implode(', ', (array) ($data['symbols'] ?? [])),
            'gift_title' => $day->gift_title ?? '',
            'gift_message' => $day->gift_message ?? '',
            'gift_link_url' => $day->gift_link_url ?? '',
            'gift_link_label' => $day->gift_link_label ?? '',
            'gift_image_url' => $day->gift_image_path !== null
                ? Storage::disk('public')->url($day->gift_image_path)
                : null,
        ];
    }
}
