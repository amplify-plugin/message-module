<?php

namespace Amplify\System\Message;

use Amplify\System\Message\Exceptions\MessengerException;
use Amplify\System\Message\Interfaces\MessageableInterface;
use Amplify\System\Message\Interfaces\MessageInterface;
use Amplify\System\Message\Interfaces\MessageThreadInterface;
use Amplify\System\Message\Interfaces\MessengerAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class Messenger implements MessengerAttachment
{
    protected $attachment;

    /**
     * Message sender.
     *
     * @var \App\Models\User
     */
    protected $from;

    /**
     * Message recipients.
     *
     * Can be an instance of MessageThread, User
     * or an array with user ids.
     *
     * @var mixed
     */
    protected $to;

    /**
     * Message.
     *
     * @var string
     */
    protected $message;

    /**
     * Attachment Title.
     *
     * @var string
     */
    protected $attachment_title;

    /**
     * Set Message.
     *
     * @param  string  $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set attachment title.
     *
     * @return $this
     */
    public function attachmentTitle($title)
    {
        $this->attachment_title = $title;

        return $this;

    }

    /**
     * Message sender.
     *
     * @param \App\Models\User
     * @return $this
     */
    public function from(MessageableInterface $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Message recipients.
     *
     * @param mixed
     * @return $this
     */
    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Send message.
     *
     * @return \Amplify\System\Message\Interfaces\MessageInterface
     *
     * @throws \Amplify\System\Message\Exceptions\MessengerException
     */
    public function send()
    {
        if (! $this->from) {
            throw new MessengerException('Sender not provided.');
        }

        if (! $this->to) {
            throw new MessengerException('Receiver not provided.');
        }

        if (! $this->message && ! $this->attachment) {
            throw new MessengerException('Message not provided');
        }

        $from = $this->from;
        $thread = $this->getThread();
        $message = $this->message;
        $attachment_title = $this->attachment_title;
        $attachment = $this->getAttachment();

        return $thread->messages()->create([
            'body' => $message,
            'attachment' => $attachment,
            'attachment_title' => $attachment_title,
            'sender_id' => $from->id,
            'model' => get_class($from),
        ]);
    }

    /**
     * Try to find a thread, if no thread is
     * found, create one.
     *
     * @return \Amplify\System\Message\Models\MessageThread
     */
    protected function getThread()
    {
        $thread = null;

        // If recipient is already a thread
        // let's use it!
        if ($this->to instanceof MessageThreadInterface) {
            $thread = $this->to;
        }

        // If recipient is a user, let's find a
        // thread between him/her and the sender.
        elseif ($this->to instanceof MessageableInterface) {
            $thread = App::make(MessageThreadInterface::class)->between($this->from, $this->to)->first();
        }

        // If recipient is an array, someone is trying
        // to send the message to multiple users...
        // Let's try to find a thread between them.
        elseif (is_array($this->to)) {
            $thread = App::make(MessageThreadInterface::class)->between(array_merge([$this->from->id], $this->to))->first();
        }

        // Return thread if was found...
        if ($thread) {
            return $thread;
        }

        return $this->createThread();
    }

    /**
     * Create thread.
     *
     * @return \Amplify\System\Message\Models\MessageThread
     */
    protected function createThread()
    {
        $from = $this->from;
        $to = $this->to;

        return DB::transaction(function () use ($from, $to) {
            $thread = App::make(MessageThreadInterface::class);
            $thread->save();

            // thread creator
            $participants = [
                ['thread_id' => $thread->id, 'user_id' => $from->id, 'model' => get_class($from)],
            ];

            if (is_numeric($to)) {
                $participants[] = ['thread_id' => $thread->id, 'user_id' => $to];
            } elseif (is_array($to)) {
                foreach ($to as $id) {
                    $participants[] = ['thread_id' => $thread->id, 'user_id' => $id];
                }
            } elseif ($to instanceof MessageableInterface) {
                $participants[] = ['thread_id' => $thread->id, 'user_id' => $to->id, 'model' => get_class($to)];
            }

            $thread->participants()->insert($participants);

            return $thread;
        });
    }

    /**
     * @return $this
     */
    public function attachment(?UploadedFile $attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasAttachment()
    {
        return $this->attachment instanceof UploadedFile;
    }

    /**
     * @return bool|string|null
     */
    protected function getAttachment()
    {
        if ($this->hasAttachment()) {
            return $file_path[] = fileUploads($this->attachment, 'messages');
        }

        return null;
    }
}
