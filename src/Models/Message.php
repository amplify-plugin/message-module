<?php

namespace Amplify\System\Message\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $fillable = [
        'thread_id',
        'sender_id',
        'body',
        'attachment_title',
        'attachment',
        'related_listing_id',
        'model',
    ];

    const ATTACHMENT_DISK = 'uploads';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });

        static::created(function ($model) {
            $model->thread()->touch();
        });
    }

    /**
     * Get sender.
     *
     * @return BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'sender_id');
    }

    /**
     * Get thread.
     *
     * @return BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(config('messenger.models.thread'), 'thread_id');
    }

    /**
     * Scope by sender.
     *
     * @param  int  $sender  User ID
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromSender($query, $sender)
    {
        return $query->where('sender_id', $sender);
    }

    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            return Storage::disk(self::ATTACHMENT_DISK)->url($this->attachment);
        }

        return $this->attachment;
    }

    public function getAttachmentExtensionAttribute()
    {
        return Str::lower(pathinfo(Storage::disk(self::ATTACHMENT_DISK)->path($this->attachment), 4));
    }

    public function getAttachmentNameAttribute()
    {
        return Str::lower(pathinfo(Storage::disk(self::ATTACHMENT_DISK)->path($this->attachment), 2));
    }
}
