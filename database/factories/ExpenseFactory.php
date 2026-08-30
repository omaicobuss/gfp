<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

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
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'date' => fake()->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'status' => 'pending',
            'paid_at' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the expense is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now()->toDateString(),
        ]);
    }

    /**
     * Indicate that the expense is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'date' => now()->subDays(5)->toDateString(),
            'paid_at' => null,
        ]);
    }
}
