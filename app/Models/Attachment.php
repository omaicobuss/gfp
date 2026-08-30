<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    /**
     * Maximum allowed size in bytes (10MB) (FR-016, FR-018, FR-019).
     */
    public const MAX_SIZE_BYTES = 10 * 1024 * 1024; // 10MB

    /**
     * Allowed MIME types for documents and receipts.
     */
    public const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/jpg',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'attachable_type',
        'attachable_id',
        'type',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * User who owns the attachment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning attachable model (RecurringExpense or RecurringExpenseOccurrence).
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the attachment is an image (JPG/PNG).
     */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/jpg']);
    }

    /**
     * Check if the attachment is a PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Human-readable file size (e.g. 1.25 MB, 450 KB).
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Delete the physical file from disk when the model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (Attachment $attachment) {
            if (Storage::disk('local')->exists($attachment->file_path)) {
                Storage::disk('local')->delete($attachment->file_path);
            }
        });
    }
}
