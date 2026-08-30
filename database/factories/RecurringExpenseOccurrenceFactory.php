<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\RecurringExpense;
use App\Models\RecurringExpenseOccurrence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecurringExpenseOccurrence>
 */
class RecurringExpenseOccurrenceFactory extends Factory
{
    protected $model = RecurringExpenseOccurrence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'recurring_expense_id' => function (array $attributes) {
                return RecurringExpense::factory()->create(['user_id' => $attributes['user_id']])->id;
            },
            'category_id' => function (array $attributes) {
                return Category::factory()->create(['user_id' => $attributes['user_id']])->id;
            },
            'description' => fake()->words(3, true),
            'due_date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'expected_amount' => fake()->randomFloat(2, 50, 1000),
            'actual_amount' => null,
            'status' => 'pending',
            'paid_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the occurrence is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'actual_amount' => $attributes['expected_amount'],
            'status' => 'paid',
            'paid_at' => now()->toDateString(),
        ]);
    }

    /**
     * Indicate that the occurrence is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'overdue',
            'actual_amount' => null,
            'paid_at' => null,
        ]);
    }
}
