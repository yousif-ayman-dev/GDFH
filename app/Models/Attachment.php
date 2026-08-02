<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'uploaded_by',
        'attachable_type',
        'attachable_id',
        'original_name',
        'stored_name',
        'disk',
        'path',
        'mime_type',
        'extension',
        'size',
        'checksum',
        'visibility',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Attachment $attachment) {
            if ($attachment->user_id && ! $attachment->uploaded_by) {
                $attachment->uploaded_by = $attachment->user_id;
            } elseif ($attachment->uploaded_by && ! $attachment->user_id) {
                $attachment->user_id = $attachment->uploaded_by;
            }
        });
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachmentable(): MorphTo
    {
        return $this->morphTo('attachable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
