<?php

namespace Amplify\System\Message\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Messageable
{
    public function threads()
    {
        return $this->belongsToMany(
            config('messenger.models.thread'),
            'message_thread_participants',
            'user_id',
            'thread_id'
        )->withPivot('last_read', 'model')
            ->wherePivot('model', __CLASS__)
            ->orderBy('updated_at', 'desc');
    }

    /**
     * Scope user existing thread.
     *
     * @param  int  $thread_id
     * @return Builder
     */
    public function scopeFindThread($query, $thread_id)
    {
        return $this->threads()->where('thread_id', $thread_id);
    }

    /**
     * Get all messages sent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messagesSent()
    {
        return $this->hasMany(config('messenger.models.message'), 'sender_id');
    }

    /**
     * Get count of all unread messages.
     *
     * @return int
     */
    public function getUnreadMessagesCountAttribute()
    {
        $count = 0;

        $this->threads()->withCount(['messages as unread_messages_count' => function ($query) {
            $query->where('sender_id', '!=', $this->id)
                ->whereRaw('created_at > message_thread_participants.last_read');
        }])->chunk(200, function ($threads) use (&$count) {
            $count += $threads->sum('unread_messages_count');
        });

        return $count;
    }

    /**
     * Mark user thread as read.
     */
    public function markThreadAsRead($thread_id)
    {
        $this->threads()->updateExistingPivot($thread_id, [
            'last_read' => $this->freshTimestamp(),
        ]);
    }
}
