<?php

namespace Amplify\System\Message\Models;

use Amplify\System\Message\Interfaces\MessageThreadInterface;
use App\Models\Contact;
use App\Models\User;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Route;

class MessageThread extends Model implements MessageThreadInterface
{
    use CrudTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'last_read',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var array
     */
    public $timestamps = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $dates = ['last_read'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['messages'];

    protected $appends = ['title', 'lastMessage', 'unreadMessagesCount'];

    /**
     * Get thread messages.
     *
     * @return HasMany
     */
    public function messages()
    {
        return $this->hasMany(config('messenger.models.message'), 'thread_id');
    }

    /**
     * Get thread participants.
     *
     * @return HasMany
     */
    public function participants()
    {
        return $this->hasMany(config('messenger.models.participant'), 'thread_id');
    }

    /**
     * Get thread title.
     *
     * IMPORTANT: participants.user relation
     * must be loaded when working with
     * multiple results!
     *
     * @return null|Model
     */
    public function getSenderAttribute()
    {
        if (in_array('admin', Route::current()->gatherMiddleware())) {
            $excludeUser = backpack_user()->id;
            $excludeModel = User::class;
        } else {
            $excludeUser = customer(true)->id;
            $excludeModel = Contact::class;
        }

        $sender = null;

        foreach ($this->participants as $participant) {
            // Exclude creator...
            if ($participant->user_id == $excludeUser && $participant->model == $excludeModel) {
                continue;
            }

            $sender = $participant->model::find($participant->user_id);
        }

        return $sender;
    }

    /**
     * Get thread last message.
     *
     * IMPORTANT: messages relation must be
     * loaded when working with multiple results!
     *
     * @return HasOne
     */
    public function getLastMessageAttribute()
    {
        return $this->messages->sortBy('created_at')->last();
    }

    /**
     * Get count of all unread messages in thread.
     *
     * For this to work you need to load the threads
     * through the user relation. For example:
     * $user->threads or
     * User::with('threads') etc.
     *
     * @return int|null
     */
    public function getUnreadMessagesCountAttribute()
    {
        // We need the pivot relation
        if (! $this->relationLoaded('pivot')) {
            return null;
        }

        $last_read = $this->pivot->last_read;
        $user_id = $this->pivot->user_id;
        $model = $this->pivot->model;

        // If message date is greater than the
        // last_read, the message is unread.
        return $this->messages->filter(function ($msg, $key) use ($last_read, $user_id, $model) {
            // Exclude messages that were sent
            // by this user.
            if ($user_id == $msg->sender_id && $model == $msg->model) {
                return false;
            }

            // If last_read is null this means
            // all messages are unread since
            // the user hasn't opened the
            // thread yet.
            if (is_null($last_read)) {
                return true;
            }

            // Return new messages only
            return $msg->created_at > $last_read;
        })->count();
    }

    /**
     * Get thread creator.
     *
     * IMPORTANT: messages and messages.sender
     * relations must be loaded when working
     * with multiple results!
     *
     * @return User|null
     */
    public function getCreatorAttribute()
    {
        return $this->messages->sortBy('created_at')->first()->sender;
    }

    /**
     * Scope threads between given users.
     *
     * @param  array  $participants  User Ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetween($query, $participants)
    {
        if (! is_array($participants)) {
            $participants = func_get_args();
            array_shift($participants);
        }

        return $query->whereHas('participants', function ($query) use ($participants) {
            $query->select('thread_id')
                ->where(function ($q) use ($participants) {
                    $q->where(['user_id' => $participants[0]->id, 'model' => get_class($participants[0])]);
                })->orWhere(function ($q) use ($participants) {
                    $q->where(['user_id' => $participants[1]->id, 'model' => get_class($participants[1])]);
                })->groupBy('thread_id')
                ->havingRaw('COUNT(thread_id) = '.count($participants));
        });
    }

    public function getCompanyAttribute()
    {
        if (in_array('admin', Route::current()->gatherMiddleware())) {
            $excludeUser = backpack_user()->id;
            $excludeModel = User::class;
        } else {
            $excludeUser = customer(true)->id;
            $excludeModel = Contact::class;
        }

        $participant_company_name = '';

        foreach ($this->participants as $participant) {
            // Exclude creator...
            if ($excludeUser && $excludeModel && $participant->user_id == $excludeUser && $participant->model == $excludeModel) {
                continue;
            }

            if ($participant->model === Contact::class) {
                $participant_company_name = $participant->model::find($participant->user_id)->customer->customer_name ?? 'Deleted Contact';
            }
        }

        return $participant_company_name ?? false;
    }
}
