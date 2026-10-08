<?php

namespace Amplify\System\Message\Http\Controllers;

use Amplify\Frontend\Traits\HasDynamicPage;
use Amplify\System\Backend\Models\Contact;
use Amplify\System\Backend\Models\User;
use Amplify\System\Message\Facades\Messenger;
use Amplify\System\Message\Http\Requests\MessageRequest;
use Amplify\System\Message\Models\Message;
use Amplify\System\Message\Models\MessageThread;
use ErrorException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

class MessageController extends Controller
{
    use HasDynamicPage;

    /**
     * Return All Message on Customer Panel
     *
     * @return string
     *
     * @throws ErrorException
     */
    public function index()
    {
        if (! customer(true)->can('message.messaging')) {
            abort(403);
        }
        $this->loadPageByType('message');

        return $this->render();
    }

    public function store(MessageRequest $request): Redirector|Application|RedirectResponse
    {
        if (! customer(true)->can('message.messaging')) {
            abort(403);
        }
        try {
            switch ($request->user_type) {
                case 'user':
                    $receiver = User::findOrFail($request->msg_to);
                    break;
                case 'contact':
                    $receiver = Contact::findOrFail($request->msg_to);
                    break;
            }

            $sender = ($request->boolean('as_customer')) ? customer(true) : backpack_user();

            $attachmentTitle = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $attachmentTitle = $file->getClientOriginalName();
            }

            $message = Messenger::from($sender)
                ->to($receiver)
                ->attachmentTitle($attachmentTitle)
                ->message($request->msg)
                ->attachment($request->file('attachment'))
                ->send();

            ($message instanceof Message)
                ? \Alert::success('Message Send Successfully')
                : \Alert::error('Something went wrong');

            $url = ($request->boolean('as_customer')) ? route('frontend.messages.show', $message->thread_id) : backpack_url('message', $message->thread_id);

            return ($message instanceof Message)
                ? redirect($url)
                : redirect()->back()->with('error', 'Something went wrong');
        } catch (\Exception $exception) {
            \Alert::error($exception->getMessage());

            return redirect()->back()->with('error', $exception->getMessage());
        }
    }

    /**
     * @return Application|Factory|View
     *
     * @throws ErrorException
     */
    public function show($id)
    {
        if (! customer(true)->can('message.messaging')) {
            abort(403);
        }

        return $this->index();
    }

    public function update(MessageRequest $request, $id): JsonResponse|RedirectResponse
    {
        if (! customer(true)->can('message.messaging')) {
            abort(403);
        }

        $from = $request->boolean('as_customer') ? $this->messagingContact() : backpack_user();
        $thread = $request->boolean('as_customer')
            ? $this->readableThread($from, (int) $id)
            : MessageThread::findOrFail($id);

        $attachmentTitle = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentTitle = $file->getClientOriginalName();
        }

        $saved = Messenger::from($from)
            ->to($thread)->attachmentTitle($attachmentTitle)
            ->message($request->msg)
            ->attachment($request->file('attachment'))
            ->send();

        if ($request->expectsJson() && $from instanceof Contact && $saved instanceof Message) {
            return response()->json([
                'message' => $this->presentMessage($saved, $from),
            ]);
        }

        return back();
    }

    public function recent(): JsonResponse
    {
        $contact = $this->messagingContact();

        $threads = $contact->threads
            ->map(function (MessageThread $thread) use ($contact) {
                $last = $thread->lastMessage;

                if (! $last || ! $last->created_at) {
                    return null;
                }

                $sender = $thread->sender;
                $title = $sender->name ?? 'Deleted User';
                $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($last->body ?? ''))));

                return [
                    'id' => (int) $thread->id,
                    'title' => $title,
                    'preview' => $text !== '' ? \Illuminate\Support\Str::limit($text, 42) : ($last->attachment ? 'Attachment' : ''),
                    'unread' => (int) ($thread->unreadMessagesCount ?? 0),
                    'sent_at' => $last->created_at->toIso8601String(),
                    'image' => $sender ? $sender->avatarImage() : '',
                    'url' => route('frontend.messages.show', $thread->id),
                ];
            })
            ->filter()
            ->sortByDesc('sent_at')
            ->values();

        return response()->json([
            'threads' => $threads,
        ])->header('Cache-Control', 'no-store');
    }

    public function live(Request $request, int $message): JsonResponse
    {
        $contact = $this->messagingContact();
        $thread = $this->readableThread($contact, $message);
        $contact->markThreadAsRead($thread->id);

        $afterId = max(0, (int) $request->query('after', 0));

        $messages = $thread->messages()
            ->where('messages.id', '>', $afterId)
            ->orderBy('messages.id')
            ->limit(100)
            ->get()
            ->map(fn (Message $item) => $this->presentMessage($item, $contact))
            ->values();

        return response()->json([
            'messages' => $messages,
        ])->header('Cache-Control', 'no-store');
    }

    private function messagingContact(): Contact
    {
        $contact = customer(true);

        if (! $contact instanceof Contact || ! $contact->can('message.messaging')) {
            abort(403);
        }

        return $contact;
    }

    private function readableThread(Contact $contact, int $threadId): MessageThread
    {
        $thread = MessageThread::query()->with('participants')->find($threadId);

        if (! $thread instanceof MessageThread) {
            abort(404);
        }

        $allowed = $thread->participants->contains(function ($participant) use ($contact) {
            return $participant->model === Contact::class
                && (int) $participant->user_id === (int) $contact->id;
        });

        if (! $allowed) {
            abort(403);
        }

        return $thread;
    }

    /**
     * @return array<string, mixed>
     */
    private function presentMessage(Message $message, Contact $contact): array
    {
        $stored = (string) ($message->getRawOriginal('attachment') ?: '');
        $url = (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://') || str_starts_with($stored, '/'))
            ? $stored
            : (string) ($message->attachment_url ?: $stored);
        $isImage = $url !== '' && (bool) preg_match('/\.(jpe?g|png|gif|webp)(\?.*)?$/i', $url);

        return [
            'id' => (int) $message->id,
            'body' => (string) ($message->body ?? ''),
            'mine' => $message->model === Contact::class && (int) $message->sender_id === (int) $contact->id,
            'time' => $message->created_at?->diffForHumans() ?? '',
            'sent_at' => $message->created_at?->toIso8601String() ?? '',
            'attachment' => $url !== '' ? [
                'url' => $url,
                'name' => $message->attachment_title ?: 'Attachment',
                'is_image' => $isImage,
            ] : null,
        ];
    }
}
