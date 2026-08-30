<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class RecurringExpense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'description',
        'expected_amount',
        'frequency',
        'frequency_days',
        'due_day',
        'due_date',
        'is_active',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
            'due_date' => 'date',
            'is_active' => 'boolean',
            'frequency_days' => 'integer',
            'due_day' => 'integer',
        ];
    }

    /**
     * User who owns the recurring expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category of the recurring expense.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Generated payment occurrences for this recurring expense.
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(RecurringExpenseOccurrence::class);
    }

    /**
     * All attachments.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * The single billing document attached (FR-016).
     */
    public function billingDocument(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable')->where('type', 'billing_document');
    }

    /**
     * Accessor for formatted currency in BRL (R$ 1.234,56).
     */
    public function getFormattedExpectedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->expected_amount, 2, ',', '.');
    }

    /**
     * Human-readable label for frequency (FR-015).
     */
    public function getFrequencyLabelAttribute(): string
    {
        return match ($this->frequency) {
            'weekly' => 'Semanal',
            'monthly' => $this->due_day ? "Mensal (dia {$this->due_day})" : 'Mensal',
            'yearly' => 'Anual',
            'custom' => "A cada {$this->frequency_days} dias",
            default => ucfirst($this->frequency),
        };
    }

    /**
     * Calculate next due date starting from a given date or current due_date.
     */
    public function calculateNextDueDate(?Carbon $fromDate = null): Carbon
    {
        $base = $fromDate ? $fromDate->copy() : $this->due_date->copy();

        return match ($this->frequency) {
            'weekly' => $base->addWeek(),
            'monthly' => $this->due_day
                ? $base->addMonthNoOverflow()->day(min($this->due_day, $base->daysInMonth))
                : $base->addMonth(),
            'yearly' => $base->addYear(),
            'custom' => $base->addDays($this->frequency_days ?: 30),
            default => $base->addMonth(),
        };
    }

    /**
     * Scope for a specific user (FR-024).
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('recurring_expenses.user_id', $userId);
    }

    /**
     * Scope for active recurring expenses.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('recurring_expenses.is_active', true);
    }

    /**
     * Scope for category filter.
     */
    public function scopeByCategory(Builder $query, ?int $categoryId): Builder
    {
        if ($categoryId) {
            $query->where('recurring_expenses.category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Scope for frequency filter.
     */
    public function scopeByFrequency(Builder $query, ?string $frequency): Builder
    {
        if ($frequency) {
            $query->where('recurring_expenses.frequency', $frequency);
        }

        return $query;
    }

    /**
     * Scope for search term.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term) {
            $query->where('recurring_expenses.description', 'like', "%{$term}%");
        }

        return $query;
    }

    /**
     * Delete attachments and occurrences when recurring expense is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (RecurringExpense $recurringExpense) {
            foreach ($recurringExpense->attachments as $attachment) {
                $attachment->delete();
            }

            foreach ($recurringExpense->occurrences as $occurrence) {
                $occurrence->delete();
            }
        });
    }
}
