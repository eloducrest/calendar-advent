<?php

namespace App\Http\Requests\Calendar;

use App\Models\CalendarDay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * @property string $challenge_type
 * @property string|null $riddle_question
 * @property string|null $riddle_answers
 * @property string|null $qcm_question
 * @property array<int, string>|null $qcm_options
 * @property int|null $qcm_correct_index
 * @property string|null $game_title
 * @property string|null $game_instructions
 * @property string|null $game_symbols
 * @property string $available_at
 * @property string|null $gift_title
 * @property string|null $gift_message
 * @property string|null $gift_link_url
 * @property string|null $gift_link_label
 * @property UploadedFile|null $gift_image
 * @property bool $remove_gift_image
 */
class UpdateCalendarDayRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'challenge_type' => ['required', 'in:riddle,qcm,game'],
            'available_at' => ['required', 'date'],

            'riddle_question' => ['required_if:challenge_type,riddle', 'nullable', 'string', 'max:500'],
            'riddle_answers' => ['required_if:challenge_type,riddle', 'nullable', 'string', 'max:2000'],

            'qcm_question' => ['required_if:challenge_type,qcm', 'nullable', 'string', 'max:500'],
            'qcm_options' => ['required_if:challenge_type,qcm', 'nullable', 'array', 'size:3'],
            'qcm_options.*' => ['required', 'string', 'max:255'],
            'qcm_correct_index' => ['required_if:challenge_type,qcm', 'nullable', 'integer', 'between:0,2'],

            'game_title' => ['required_if:challenge_type,game', 'nullable', 'string', 'max:120'],
            'game_instructions' => ['nullable', 'string', 'max:500'],
            'game_symbols' => ['nullable', 'string', 'max:255'],

            'gift_title' => ['nullable', 'string', 'max:120'],
            'gift_message' => ['nullable', 'string', 'max:2000'],
            'gift_link_url' => ['nullable', 'string', 'max:2048'],
            'gift_link_label' => ['nullable', 'string', 'max:120'],
            'gift_image' => ['nullable', 'image', 'max:4096'],
            'remove_gift_image' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Build the challenge_data payload from the flat form fields.
     *
     * @return array<string, mixed>
     */
    public function challengeData(): array
    {
        return match ($this->string('challenge_type')->value()) {
            CalendarDay::CHALLENGE_QCM => [
                'question' => $this->string('qcm_question')->value(),
                'options' => array_values((array) $this->input('qcm_options', [])),
                'correct_index' => (int) $this->integer('qcm_correct_index'),
            ],
            CalendarDay::CHALLENGE_GAME => [
                'game' => 'memory',
                'title' => $this->string('game_title')->value(),
                'instructions' => $this->string('game_instructions')->value(),
                'symbols' => array_values(array_filter(array_map(
                    'trim',
                    explode(',', $this->string('game_symbols')->value()),
                ))),
            ],
            default => [
                'question' => $this->string('riddle_question')->value(),
                'accepted_answers' => array_values(array_filter(array_map(
                    'trim',
                    explode("\n", str_replace("\r\n", "\n", $this->string('riddle_answers')->value())),
                ))),
            ],
        };
    }
}
