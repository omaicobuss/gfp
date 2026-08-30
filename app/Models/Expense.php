<?php

namespace App\Models;

use App\Services\ExpenseStatusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
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
        'amount',
        'date',
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
            'amount' => 'decimal:2',
            'date' => 'date',
            'paid_at' => 'date',
        ];
    }

    /**
     * User who owns the expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category of the expense.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Accessor for formatted currency in BRL (R$ 1.234,56).
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Accessor for status human label.
     */
    public function getStatusLabelAttribute(): string
    {
        return ExpenseStatusService::getStatusLabel($this->status);
    }

    /**
     * Accessor for status badge CSS classes.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return ExpenseStatusService::getStatusBadgeClasses($this->status);
    }

    /**
     * Check if expense is paid.
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
        return $query->where('expenses.user_id', $userId);
    }

    /**
     * Scope for date period filter (FR-030).
     */
    public function scopeByPeriod(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start) {
            $query->whereDate('expenses.date', '>=', $start);
        }

        if ($end) {
            $query->whereDate('expenses.date', '<=', $end);
        }

        return $query;
    }

    /**
     * Scope for category filter (FR-030).
     */
    public function scopeByCategory(Builder $query, ?int $categoryId): Builder
    {
        if ($categoryId) {
            $query->where('expenses.category_id', $categoryId);
        }

        return $query;
    }

    /**
     * Scope for status filter (FR-030).
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if ($status && in_array($status, ['paid', 'pending', 'overdue'])) {
            $query->where('expenses.status', $status);
        }

        return $query;
    }

    /**
     * Scope for search keyword.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term) {
            $query->where('expenses.description', 'like', "%{$term}%");
        }

        return $query;
    }
}
