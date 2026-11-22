<?php

namespace Database\Factories;

use App\Models\CalendarDay;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CalendarDay>
 */
class CalendarDayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day' => fake()->unique()->numberBetween(1, 24),
            'available_at' => Carbon::createFromDate(null, 12, 1)->addDays(fake()->numberBetween(0, 23)),
            'challenge_type' => CalendarDay::CHALLENGE_RIDDLE,
            'challenge_data' => [
                'question' => fake()->sentence(),
                'accepted_answers' => [fake()->word()],
            ],
            'gift_title' => fake()->words(3, true),
            'gift_message' => fake()->paragraph(),
            'gift_image_path' => null,
            'gift_link_url' => null,
            'gift_link_label' => null,
        ];
    }

    public function qcm(): static
    {
        return $this->state(fn (array $attributes): array => [
            'challenge_type' => CalendarDay::CHALLENGE_QCM,
            'challenge_data' => [
                'question' => fake()->sentence(),
                'options' => [fake()->word(), fake()->word(), fake()->word()],
                'correct_index' => fake()->numberBetween(0, 2),
            ],
        ]);
    }

    public function game(): static
    {
        return $this->state(fn (array $attributes): array => [
            'challenge_type' => CalendarDay::CHALLENGE_GAME,
            'challenge_data' => [
                'game' => 'memory',
                'title' => 'Trouve les paires',
                'instructions' => 'Retourne les cartes et retrouve les paires pour débloquer le cadeau.',
            ],
        ]);
    }

    public function availableOn(Carbon $date): static
    {
        return $this->state(fn (array $attributes): array => [
            'available_at' => $date,
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes): array => [
            'available_at' => Carbon::tomorrow(),
        ]);
    }
}
