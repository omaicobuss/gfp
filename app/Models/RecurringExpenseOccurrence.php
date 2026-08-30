<?php

namespace App\Models;

use App\Services\ExpenseStatusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class RecurringExpenseOccurrence extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'recurring_expense_id',
        'category_id',
        'description',
        'due_date',
        'expected_amount',
        'actual_amount',
        'status',
        'paid_at',
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
            'actual_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    /**
     * User who owns the occurrence.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The parent recurring expense template.
     */
    public function recurringExpense(): BelongsTo
    {
        return $this->belongsTo(RecurringExpense::class);
    }

    /**
     * Category of the occurrence.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * All attachments associated with this occurrence.
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * The single payment receipt attached to this occurrence (FR-018).
     */
    public function paymentReceipt(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable')->where('type', 'payment_receipt');
    }

    /**
     * Effective amount (actual amount if paid/specified, else expected amount).
     */
    public function getAmountAttribute(): float
    {
        return (float) ($this->actual_amount ?? $this->expected_amount);
    }

    /**
     * Formatted currency string for display.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Formatted expected amount.
     */
    public function getFormattedExpectedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->expected_amount, 2, ',', '.');
    }

    /**
     * Formatted actual amount.
     */
    public function getFormattedActualAmountAttribute(): ?string
    {
        return $this->actual_amount !== null
            ? 'R$ ' . number_format($this->actual_amount, 2, ',', '.')
            : null;
    }

    /**
     * Human-readable label for status (FR-020).
     */
    public function getStatusLabelAttribute(): string
    {
        return ExpenseStatusService::getStatusLabel($this->status);
    }

    /**
     * Badge CSS classes for status.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return ExpenseStatusService::getStatusBadgeClasses($this->status);
    }

    /**
     * Check if occurrence is paid.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid' || ! is_null($this->paid_at);
    }

    /**
     * Scope for a specific user (FR-024).
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('recurring_expense_occurrences.user_id', $userId);
    }

    /**
     * Scope for date period filter (FR-030).
     */
    public function scopeByPeriod(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start) {
            $query->whereDate('recurring_expense_occurrences.due_date', '>=', $start);
        }

        if ($end) {
            $query->whereDate('recurring_expense_occurrences.due_date', '<=', $end);
        }

        return $query;
    }

    /**
     * Scope for category filter (FR-030).
     */
    public function scopeByCategory(Builder $query, ?int $categoryId): Builder
    {
        if ($categoryId) {
            $query->where('recurring_expense_occurrences.category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Scope for status filter (FR-030).
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if ($status && in_array($status, ['paid', 'pending', 'overdue'])) {
            $query->where('recurring_expense_occurrences.status', $status);
        }

        return $query;
    }

    /**
     * Scope for search keyword.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term) {
            $query->where('recurring_expense_occurrences.description', 'like', "%{$term}%");
        }

        return $query;
    }

    /**
     * Delete attachments when occurrence is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (RecurringExpenseOccurrence $occurrence) {
            foreach ($occurrence->attachments as $attachment) {
                $attachment->delete();
            }
        });
    }
}
