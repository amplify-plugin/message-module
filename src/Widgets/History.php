<?php

namespace Amplify\System\Message\Widgets;

use Amplify\System\Message\Models\MessageThread;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class History extends Component
{
    private ?MessageThread $currentThread;

    public Collection $messages;

    /**
     * Create a new component instance.
     *
     * @param  null  $current
     */
    public function __construct(public bool $asCustomer = false, $current = null)
    {
        $this->messages = collect();

        $this->currentThread = $current;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return Application|Factory|View
     */
    public function render()
    {
        $reader = ($this->asCustomer) ? customer(true) : backpack_user();

        $this->loadThreadMessages();

        return view('message::message.history', [
            'reader_user' => $reader,
            'thread' => $this->currentThread,
        ]);
    }

    public function loadThreadMessages(): void
    {
        $this->currentThread?->messages->each(function ($message) {
            $this->messages->push($message);
        });
    }

    /**
     * @return string
     */
    public function threadTitle()
    {
        return ($this->currentThread) ? ($this->currentThread->sender->name ?? 'Deleted User') : 'New Message';
    }

    /**
     * @return string
     */
    public function theadImage()
    {
        $sender = $this->currentThread?->sender;

        if ($sender == null) {
            return 'https://ui-avatars.com/api/?background=cef2ef&rounded=true&name='.urlencode($sender->name ?? 'N M');
        }

        return $sender->avatarImage();

    }

    public function threadLink(): string
    {
        if ($this->asCustomer) {
            return ($this->currentThread) ? route('frontend.messages.update', $this->currentThread->id) : route('frontend.messages.store');
        }

        return ($this->currentThread) ? route('message.update', $this->currentThread->id) : route('message.store');
    }

    public function hasMessagingPermission()
    {
        if ($this->asCustomer) {
            return customer(true)->can('message.messaging') || customer(true)->can('message.messaging');
        }

        return true;
    }
}
