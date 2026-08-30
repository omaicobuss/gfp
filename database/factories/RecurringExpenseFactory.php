<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\RecurringExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringExpense>
 */
class RecurringExpenseFactory extends Factory
{
    protected $model = RecurringExpense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => function (array $attributes) {
                return Category::factory()->create(['user_id' => $attributes['user_id']])->id;
            },
            'description' => fake()->words(3, true),
            'expected_amount' => fake()->randomFloat(2, 50, 2000),
            'frequency' => 'monthly',
            'frequency_days' => null,
            'due_day' => 10,
            'due_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Set weekly frequency.
     */
    public function weekly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'weekly',
            'due_day' => null,
            'frequency_days' => null,
        ]);
    }

    /**
     * Set yearly frequency.
     */
    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'yearly',
            'due_day' => null,
            'frequency_days' => null,
        ]);
    }

    /**
     * Set custom days frequency.
     */
    public function custom(int $days = 45): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'custom',
            'due_day' => null,
            'frequency_days' => $days,
        ]);
    }
}
