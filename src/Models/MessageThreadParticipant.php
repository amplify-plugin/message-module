<?php

namespace Amplify\System\Message\Models;

use Amplify\System\Message\Interfaces\MessageThreadParticipantInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageThreadParticipant extends Model implements MessageThreadParticipantInterface
{
    use SoftDeletes;

    protected $fillable = [
        'thread_id',
        'user_id',
        'model',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $dates = ['deleted_at'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var array
     */
    public $timestamps = false;

    /**
     * Get thread.
     *
     * @return BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(config('messenger.models.thread'));
    }

    /**
     * Get user.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
}
