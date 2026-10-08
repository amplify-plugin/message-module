<?php

namespace Amplify\System\Message\View\Components\Customer;

use Amplify\Frontend\Abstracts\BaseComponent;
use Amplify\System\Backend\Models\Contact;
use Amplify\System\Message\Models\MessageThread;
use Closure;
use Illuminate\Contracts\View\View;

class Message extends BaseComponent
{
    public function shouldRender(): bool
    {
        return true;
    }

    public function render(): View|Closure|string
    {
        $contact = customer(true);

        if (! $contact instanceof Contact || ! $contact->can('message.messaging')) {
            abort(403);
        }

        $threadId = request()->route('message');
        $current = null;

        if (is_numeric($threadId)) {
            $current = MessageThread::query()->with('participants')->find($threadId);

            if (! $current instanceof MessageThread) {
                abort(404);
            }

            $allowed = $current->participants->contains(function ($participant) use ($contact) {
                return $participant->model === Contact::class
                    && (int) $participant->user_id === (int) $contact->id;
            });

            if (! $allowed) {
                abort(403);
            }
        }

        if ($current instanceof MessageThread) {
            $contact->markThreadAsRead($current->id);
            $contact->unsetRelation('threads');
        }

        return view('message::customer.index', [
            'threads' => $contact->threads,
            'current' => $current,
        ]);
    }
}
