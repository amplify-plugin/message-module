@php
    use Amplify\System\Backend\Models\Contact;
    use Amplify\System\Backend\Models\User;
    $contact = customer(true);
@endphp
<div {!! $htmlAttributes !!}>
    <style>
        .customer-messages {
            display: flex;
            height: clamp(420px, calc(100vh - 220px), 720px);
            min-height: 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        .customer-messages__list {
            width: 280px;
            flex: 0 0 280px;
            border-right: 1px solid #eaeaea;
            background: #fff;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .customer-messages__list h4 { font-size: 16px; }
        .customer-messages__threads { flex: 1 1 auto; overflow-y: auto; min-height: 0; }
        .customer-messages__thread {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: inherit;
            text-decoration: none;
            border-radius: 6px;
            margin: 0 8px 4px;
        }
        .customer-messages__thread:hover { background: #efefef; text-decoration: none; color: inherit; }
        .customer-messages__thread.is-active { background: #efefef; }
        .customer-messages__thread.is-unread .customer-messages__name,
        .customer-messages__thread.is-unread .customer-messages__preview { font-weight: 700; }
        .customer-messages__avatar,
        .customer-messages__bubble-avatar {
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; line-height: 1; flex-shrink: 0;
        }
        .customer-messages__avatar {
            width: 46px; height: 46px; flex: 0 0 46px;
            background: #dfe3ea; color: #1b2a4e; font-size: 14px;
        }
        .customer-messages__bubble-avatar {
            width: 36px; height: 36px; margin-top: 4px; font-size: 12px; color: #fff;
        }
        .customer-messages__bubble-avatar.is-them { background: #6c757d; margin-right: 8px; }
        .customer-messages__bubble-avatar.is-mine { background: #17a2b8; margin-left: 8px; }
        .customer-messages__scroll p { text-align: left; }
        .customer-messages__meta { min-width: 0; flex: 1 1 auto; }
        .customer-messages__name {
            font-size: 14px; line-height: 1.3; margin: 0; color: #212529;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .customer-messages__preview {
            margin: 2px 0 0; color: #6c757d; font-size: 12px; line-height: 1.3;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .customer-messages__time { display: block; margin-top: 2px; color: #999; font-size: 11px; }
        .customer-messages__time i { margin-right: 3px; }
        .customer-messages__pane {
            flex: 1 1 auto;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .customer-messages .chat-stage {
            position: relative;
            flex: 1 1 0%;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        .customer-messages__scroll {
            flex: 1 1 0%;
            height: 0;
            min-height: 0;
            overflow-y: auto;
            overflow-anchor: none;
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .customer-messages .chat-boot {
            display: none;
            position: absolute;
            inset: 0;
            z-index: 3;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            color: #C31E1E;
        }
        .customer-messages .chat-stage.is-loading .chat-boot { display: flex; }
        .customer-messages .chat-booting > * { visibility: hidden; }
        .customer-messages .chat-boot__spinner {
            width: 28px;
            height: 28px;
            border: 3px solid rgba(195, 30, 30, 0.2);
            border-top-color: currentColor;
            border-radius: 50%;
            animation: customer-chat-boot-spin .7s linear infinite;
        }
        @keyframes customer-chat-boot-spin { to { transform: rotate(360deg); } }
        .customer-messages .ticket-composer { display: flex; align-items: center; gap: 12px; }
        .customer-messages .ticket-composer__btn {
            flex: 0 0 44px; width: 44px !important; height: 44px !important; margin: 0 !important;
            padding: 0 !important; line-height: 1 !important; border-radius: 4px !important;
        }
        .customer-messages .ticket-composer .btn-primary:focus,
        .customer-messages .ticket-composer .btn-primary:focus-visible,
        .customer-messages .ticket-composer .btn-primary:active,
        .customer-messages .ticket-composer .btn-primary:disabled,
        .customer-messages .ticket-composer .btn-primary.disabled {
            outline: none !important;
            border-color: #C31E1E !important;
            box-shadow: 0 0 0 0.2rem rgba(195, 30, 30, 0.35) !important;
        }
        .customer-messages textarea.ticket-composer__input {
            flex: 1 1 auto; width: 100%; height: 44px !important; min-height: 44px !important; max-height: 44px !important;
            margin: 0 !important; padding: 4px 14px !important; line-height: 16px !important; font-size: 14px !important;
            border-radius: 4px !important; overflow-y: auto; resize: none; box-sizing: border-box;
        }
        .customer-messages .chat-file-input {
            position: absolute !important; width: 1px !important; height: 1px !important;
            padding: 0 !important; margin: -1px !important; overflow: hidden !important;
            clip: rect(0,0,0,0) !important; border: 0 !important;
        }
        .customer-messages .message-file-pick {
            display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
            padding: 4px 6px 4px 4px; border: 1px solid #e4e6eb; border-radius: 10px; background: #f8f9fb;
            max-width: 280px;
        }
        .customer-messages .message-file-pick img,
        .customer-messages .message-file-pick__icon {
            width: 36px; height: 36px; flex: 0 0 36px; border-radius: 6px; object-fit: cover;
        }
        .customer-messages .message-file-pick__icon {
            display: inline-flex; align-items: center; justify-content: center;
            background: #e6e9ef; color: #3d4d6a;
        }
        .customer-messages .message-file-pick__name {
            display: block; max-width: 160px; overflow: hidden; text-overflow: ellipsis;
            white-space: nowrap; font-size: 13px; font-weight: 600; color: #1b2a4e;
        }
        .customer-messages .message-file-pick__size { display: block; font-size: 11px; color: #6c757d; }
        .customer-messages .message-file-pick img[hidden],
        .customer-messages .message-file-pick__icon[hidden] { display: none !important; }
        .customer-messages .chat-spinner {
            width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.35);
            border-top-color: #fff; border-radius: 50%; display: inline-block;
            animation: customer-message-spin .7s linear infinite;
        }
        .customer-messages .chat-spinner[hidden],
        .customer-messages [data-send-icon][hidden] { display: none !important; }
        @keyframes customer-message-spin { to { transform: rotate(360deg); } }
        @media (max-width: 767.98px) {
            .customer-messages { flex-direction: column; height: clamp(360px, calc(100vh - 160px), 640px); }
            .customer-messages__list { width: 100%; flex-basis: auto; max-height: 220px; border-right: 0; border-bottom: 1px solid #eaeaea; }
        }
    </style>

    <div class="card border-0 shadow">
        <div class="customer-messages">
            <aside class="customer-messages__list"
                   data-recent-url="{{ route('frontend.messages.recent') }}"
                   data-active-thread="{{ $current->id ?? '' }}">
                <h4 class="px-3 pt-3 mb-2">Messages</h4>
                <div class="customer-messages__threads">
                    @forelse ($threads as $thread)
                        @continue(! $thread->lastMessage)
                        @php
                            $title = $thread->sender->name ?? 'Deleted User';
                            $previewText = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($thread->lastMessage->body ?? ''))));
                            $preview = $previewText !== '' ? \Illuminate\Support\Str::limit($previewText, 42) : ($thread->lastMessage->attachment ? 'Attachment' : '');
                            $unread = (int) ($thread->unreadMessagesCount ?? 0);
                            $isActive = $current && (int) $current->id === (int) $thread->id;
                            $initials = collect(preg_split('/\s+/', trim($title)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'NA';
                        @endphp
                        <a href="{{ route('frontend.messages.show', $thread->id) }}"
                           data-thread-id="{{ $thread->id }}"
                           class="customer-messages__thread {{ $isActive ? 'is-active' : '' }} {{ $unread > 0 ? 'is-unread' : '' }}">
                            <span class="customer-messages__avatar" data-thread-initials>{{ $initials }}</span>
                            <span class="customer-messages__meta">
                                <p class="customer-messages__name">
                                    <span data-thread-title>{{ $title }}</span>
                                    <span class="badge badge-danger {{ $unread > 0 ? '' : 'd-none' }}" data-thread-unread>{{ $unread }}</span>
                                </p>
                                <p class="customer-messages__preview" data-thread-preview>{{ $preview }}</p>
                                <span class="customer-messages__time">
                                    <i class="fa fa-clock"></i>
                                    <span data-thread-time="{{ $thread->lastMessage->created_at?->toIso8601String() }}">{{ $thread->lastMessage->created_at?->diffForHumans() }}</span>
                                </span>
                            </span>
                        </a>
                    @empty
                        <p class="text-muted px-3" data-thread-empty>No messages yet.</p>
                    @endforelse
                </div>
            </aside>

            <section class="customer-messages__pane">
                @if ($current)
                    @php
                        $headerSender = $current->sender;
                        $headerName = $headerSender->name ?? 'Deleted User';
                        $headerInitials = collect(preg_split('/\s+/', trim($headerName)))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'NA';
                        $mineInitials = collect(preg_split('/\s+/', trim((string) ($contact->name ?? ''))))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'ME';
                    @endphp
                    <header class="px-4 py-3 border-bottom bg-white">
                        <div class="d-flex align-items-center">
                            <span class="customer-messages__avatar mr-3">{{ $headerInitials }}</span>
                            <h6 class="mb-0 font-weight-bold text-dark text-truncate" style="max-width: 280px;">{{ $headerName }}</h6>
                        </div>
                    </header>

                    <div class="chat-stage is-loading">
                    <div class="chat-boot" role="status" aria-label="Loading messages">
                        <span class="chat-boot__spinner"></span>
                    </div>
                    <div class="customer-messages__scroll chat-booting p-4"
                         data-live-url="{{ route('frontend.messages.live', $current->id) }}"
                         data-after="{{ (int) $current->messages->max('id') }}"
                         data-their-initials="{{ $headerInitials }}"
                         data-mine-initials="{{ $mineInitials }}">
                        @forelse ($current->messages->sortBy('id') as $message)
                            @php
                                $isMine = $message->model === Contact::class && (int) $message->sender_id === (int) $contact->id;
                                $storedFile = (string) ($message->getRawOriginal('attachment') ?: $message->attachment ?: '');
                                $fileUrl = (str_starts_with($storedFile, 'http://') || str_starts_with($storedFile, 'https://') || str_starts_with($storedFile, '/'))
                                    ? $storedFile
                                    : (string) ($message->attachment_url ?: $storedFile);
                                $isImage = $fileUrl !== '' && preg_match('/\.(jpe?g|png|gif|webp)(\?.*)?$/i', $fileUrl);
                            @endphp
                            <div class="d-flex mb-4 {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}" data-message-id="{{ $message->id }}">
                                <div style="max-width: 75%;">
                                    <div class="p-3 text-left {{ $isMine ? 'bg-primary text-white' : 'bg-white border' }}" style="border-radius: {{ $isMine ? '18px 18px 4px 18px' : '18px 18px 18px 4px' }}; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                        @if (filled($message->body))
                                            <p class="mb-0" style="margin: 0; line-height: 1.45; word-break: break-word;">{!! nl2br(e($message->body)) !!}</p>
                                        @endif
                                        @if ($fileUrl)
                                            @if ($isImage)
                                                <a href="{{ $fileUrl }}" target="_blank" class="d-inline-block rounded overflow-hidden border mt-2" style="line-height: 0;">
                                                    <img src="{{ $fileUrl }}" alt="Attachment" class="img-fluid" style="max-height: 140px; max-width: 200px; object-fit: cover;">
                                                </a>
                                            @else
                                                <a href="{{ $fileUrl }}" target="_blank" download class="d-flex align-items-center p-2 mt-2 rounded text-decoration-none {{ $isMine ? '' : 'bg-light' }}" style="{{ $isMine ? 'background: rgba(255,255,255,0.15);' : '' }}">
                                                    <span class="mr-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 6px; background: #dfe3ea; color: #3d4d6a; flex: 0 0 32px;">
                                                        <i class="fa fa-file-alt" style="font-size: 14px;"></i>
                                                    </span>
                                                    <span class="text-truncate small {{ $isMine ? 'text-white' : 'text-dark' }}">{{ $message->attachment_title ?: 'Attachment' }}</span>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1 px-2" style="font-size: 11px;">{{ $message->created_at?->diffForHumans() }}</small>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted mb-0">No messages yet.</p>
                        @endforelse
                    </div>
                    </div>

                    <footer class="border-top bg-white p-3">
                        <form action="{{ route('frontend.messages.update', $current->id) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="as_customer" value="1">
                            <div id="customer-message-file" class="message-file-pick" hidden>
                                <img data-file-preview alt="" hidden>
                                <span class="message-file-pick__icon" data-file-icon><i class="fa fa-file-alt"></i></span>
                                <span>
                                    <span class="message-file-pick__name" data-file-name></span>
                                    <span class="message-file-pick__size" data-file-size></span>
                                </span>
                                <button type="button" class="btn btn-link btn-sm text-muted p-0 ml-auto" data-file-clear aria-label="Remove file">&times;</button>
                            </div>
                            <div class="ticket-composer">
                                <button type="button" class="btn btn-light d-flex align-items-center justify-content-center ticket-composer__btn" data-file-open aria-label="Attach a file">
                                    <i class="fa fa-paperclip text-muted"></i>
                                </button>
                                <textarea name="msg" class="form-control border ticket-composer__input" rows="1" placeholder="Type your message...">{{ old('msg') }}</textarea>
                                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center ticket-composer__btn" data-send aria-label="Send">
                                    <i class="fa fa-paper-plane" data-send-icon></i>
                                    <span class="chat-spinner" data-send-spinner hidden></span>
                                </button>
                            </div>
                            @error('msg')
                                <small class="text-danger d-block mt-1 px-3">{{ $message }}</small>
                            @enderror
                            @error('attachment')
                                <small class="text-danger d-block mt-1 px-3">{{ $message }}</small>
                            @enderror
                            <div data-send-error class="text-danger small d-block mt-1 px-3"></div>
                            <input type="file" name="attachment" class="chat-file-input" data-file-input tabindex="-1" aria-hidden="true">
                        </form>
                    </footer>
                    <script>
                        (function () {
                            var form = document.querySelector('.customer-messages footer form');
                            var input = document.querySelector('[data-file-input]');
                            var open = document.querySelector('[data-file-open]');
                            var chip = document.getElementById('customer-message-file');
                            var name = chip ? chip.querySelector('[data-file-name]') : null;
                            var size = chip ? chip.querySelector('[data-file-size]') : null;
                            var preview = chip ? chip.querySelector('[data-file-preview]') : null;
                            var fileIcon = chip ? chip.querySelector('[data-file-icon]') : null;
                            var clear = chip ? chip.querySelector('[data-file-clear]') : null;
                            var previewUrl = '';
                            var send = document.querySelector('[data-send]');
                            var sendIcon = document.querySelector('[data-send-icon]');
                            var spinner = document.querySelector('[data-send-spinner]');
                            var error = document.querySelector('[data-send-error]');
                            if (!form || !input || !open || !chip || !name || !size || !preview || !fileIcon || !clear || !send) {
                                return;
                            }

                            function fileSize(bytes) {
                                if (bytes < 1024) {
                                    return bytes + ' B';
                                }
                                if (bytes < 1048576) {
                                    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
                                }
                                return (bytes / 1048576).toFixed(1) + ' MB';
                            }

                            function clearPreview() {
                                if (previewUrl) {
                                    URL.revokeObjectURL(previewUrl);
                                    previewUrl = '';
                                }
                                preview.hidden = true;
                                preview.removeAttribute('src');
                                fileIcon.hidden = false;
                            }

                            open.addEventListener('click', function () {
                                if (form.dataset.sending === '1') {
                                    return;
                                }
                                input.click();
                            });
                            input.addEventListener('change', function () {
                                var file = input.files && input.files[0];
                                clearPreview();
                                if (!file) {
                                    chip.hidden = true;
                                    name.textContent = '';
                                    size.textContent = '';
                                    return;
                                }
                                chip.hidden = false;
                                name.textContent = file.name;
                                size.textContent = fileSize(file.size);
                                if (file.type && file.type.indexOf('image/') === 0) {
                                    previewUrl = URL.createObjectURL(file);
                                    preview.src = previewUrl;
                                    preview.hidden = false;
                                    fileIcon.hidden = true;
                                }
                            });
                            clear.addEventListener('click', function () {
                                if (form.dataset.sending === '1') {
                                    return;
                                }
                                input.value = '';
                                chip.hidden = true;
                                name.textContent = '';
                                size.textContent = '';
                                clearPreview();
                            });

                            form.addEventListener('submit', function (event) {
                                event.preventDefault();
                                if (form.dataset.sending === '1') {
                                    return;
                                }
                                if (error) {
                                    error.textContent = '';
                                }
                                form.dataset.sending = '1';
                                send.disabled = true;
                                open.disabled = true;
                                clear.disabled = true;
                                if (sendIcon) {
                                    sendIcon.hidden = true;
                                }
                                if (spinner) {
                                    spinner.hidden = false;
                                }

                                var data = new FormData(form);
                                var xhr = new XMLHttpRequest();
                                xhr.open('POST', form.action);
                                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                                xhr.setRequestHeader('Accept', 'application/json');
                                xhr.upload.onprogress = function (progress) {
                                    if (!progress.lengthComputable || !progress.total || !input.files || !input.files.length) {
                                        return;
                                    }
                                    size.textContent = 'Uploading ' + Math.min(100, Math.round((progress.loaded / progress.total) * 100)) + '%';
                                };
                                function finishSend() {
                                    form.dataset.sending = '0';
                                    send.disabled = false;
                                    open.disabled = false;
                                    clear.disabled = false;
                                    if (sendIcon) {
                                        sendIcon.hidden = false;
                                    }
                                    if (spinner) {
                                        spinner.hidden = true;
                                    }
                                }
                                xhr.onload = function () {
                                    var payload = {};
                                    try {
                                        payload = JSON.parse(xhr.responseText || '{}');
                                    } catch (e) {
                                        payload = {};
                                    }
                                    if (xhr.status >= 200 && xhr.status < 300 && payload.message) {
                                        if (window.CustomerMessageChat) {
                                            window.CustomerMessageChat.append([payload.message]);
                                            window.CustomerMessageChat.refreshList();
                                        }
                                        var text = form.querySelector('[name="msg"]');
                                        if (text) {
                                            text.value = '';
                                        }
                                        input.value = '';
                                        chip.hidden = true;
                                        name.textContent = '';
                                        size.textContent = '';
                                        clearPreview();
                                        finishSend();
                                        return;
                                    }
                                    var validation = payload.errors ? Object.keys(payload.errors).reduce(function (lines, key) {
                                        return lines.concat(payload.errors[key]);
                                    }, []).join(' ') : '';
                                    if (error) {
                                        error.textContent = validation || payload.message || 'Could not send the message. Please try again.';
                                    }
                                    finishSend();
                                };
                                xhr.onerror = function () {
                                    if (error) {
                                        error.textContent = 'Could not send the message. Please try again.';
                                    }
                                    form.dataset.sending = '0';
                                    send.disabled = false;
                                    open.disabled = false;
                                    clear.disabled = false;
                                    if (sendIcon) {
                                        sendIcon.hidden = false;
                                    }
                                    if (spinner) {
                                        spinner.hidden = true;
                                    }
                                };
                                xhr.send(data);
                            });

                            var scroll = document.querySelector('.customer-messages__scroll');
                            if (scroll) {
                                scroll.scrollTop = scroll.scrollHeight;
                            }
                        })();
                    </script>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-muted h-100 py-5">
                        <div class="mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(0,0,0,0.05);">
                            <i class="fa fa-comments" style="font-size: 32px; opacity: 0.5;"></i>
                        </div>
                        <p class="mb-0">{{ $threads->isEmpty() ? 'No messages yet' : 'Select a message' }}</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
    <script>
        (function () {
            var list = document.querySelector('.customer-messages__list');
            if (!list) {
                return;
            }

            var baseMs = 3000;
            var stepMs = 2000;
            var maxMs = 30000;

            function imageReady(img) {
                return !img.getAttribute('src') || (img.complete && img.naturalHeight > 0);
            }

            function scrollToEnd(scrollEl) {
                scrollEl.scrollTop = scrollEl.scrollHeight;
                var nodes = scrollEl.querySelectorAll('[data-message-id]');
                var last = nodes[nodes.length - 1];
                if (!last) {
                    return;
                }
                var lastBox = last.getBoundingClientRect();
                var box = scrollEl.getBoundingClientRect();
                if (lastBox.bottom > box.bottom + 1) {
                    scrollEl.scrollTop += lastBox.bottom - box.bottom + 8;
                }
            }

            function lastMessageVisible(scrollEl) {
                var nodes = scrollEl.querySelectorAll('[data-message-id]');
                var last = nodes[nodes.length - 1];
                if (!last) {
                    return true;
                }
                var lastBox = last.getBoundingClientRect();
                var box = scrollEl.getBoundingClientRect();
                if (box.height < 40) {
                    return false;
                }
                return lastBox.bottom <= box.bottom + 8 && lastBox.bottom > box.top;
            }

            function pinMessageScroll(scrollEl) {
                if (!scrollEl || scrollEl.dataset.pinScroll === '1') {
                    return;
                }
                scrollEl.dataset.pinScroll = '1';
                var revealed = false;
                var revealing = false;
                var pending = 0;
                var deadline = Date.now() + 2500;
                scrollEl.classList.remove('chat-booting');

                function reveal() {
                    if (revealed || revealing) {
                        return;
                    }
                    if ((pending > 0 || !lastMessageVisible(scrollEl)) && Date.now() < deadline) {
                        return;
                    }
                    revealing = true;
                    scrollToEnd(scrollEl);
                    requestAnimationFrame(function () {
                        scrollToEnd(scrollEl);
                        requestAnimationFrame(function () {
                            scrollToEnd(scrollEl);
                            revealed = true;
                            revealing = false;
                            var stage = scrollEl.closest('.chat-stage');
                            if (stage) {
                                stage.classList.remove('is-loading');
                            }
                        });
                    });
                }

                scrollEl.querySelectorAll('img').forEach(function (img) {
                    if (imageReady(img)) {
                        return;
                    }
                    pending += 1;
                    var settled = false;
                    var done = function () {
                        if (settled) {
                            return;
                        }
                        settled = true;
                        pending = Math.max(0, pending - 1);
                        scrollToEnd(scrollEl);
                    };
                    img.addEventListener('load', done);
                    img.addEventListener('error', done);
                    if (img.decode) {
                        img.decode().then(done, done);
                    }
                });

                var timer = setInterval(function () {
                    if (revealed) {
                        clearInterval(timer);
                        return;
                    }
                    scrollToEnd(scrollEl);
                    reveal();
                }, 50);
                setTimeout(function () {
                    clearInterval(timer);
                    pending = 0;
                    deadline = 0;
                    reveal();
                }, 2500);
            }

            pinMessageScroll(document.querySelector('.customer-messages__scroll'));
            var recentDelay = baseMs;
            var liveDelay = baseMs;
            var recentTimer = null;
            var liveTimer = null;
            var recentFlight = false;
            var liveFlight = false;

            function humanTime(iso) {
                var then = Date.parse(iso);
                if (!then) {
                    return '';
                }
                var seconds = Math.max(1, Math.round((Date.now() - then) / 1000));
                var steps = [[60, 'second'], [60, 'minute'], [24, 'hour'], [7, 'day'], [4, 'week'], [12, 'month']];
                var count = seconds;
                var label = 'year';
                for (var i = 0; i < steps.length; i++) {
                    if (count < steps[i][0]) {
                        label = steps[i][1];
                        break;
                    }
                    count = Math.round(count / steps[i][0]);
                }
                if (label === 'year') {
                    count = Math.max(1, Math.round(seconds / 31536000));
                }
                return count + ' ' + label + (count === 1 ? '' : 's') + ' ago';
            }

            function refreshTimes() {
                document.querySelectorAll('[data-thread-time], [data-bubble-time]').forEach(function (node) {
                    var text = humanTime(node.getAttribute('data-thread-time') || node.getAttribute('data-bubble-time'));
                    if (text) {
                        node.textContent = text;
                    }
                });
            }

            function escapeHtml(value) {
                return String(value == null ? '' : value)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function applyThread(row, thread) {
                var active = String(list.getAttribute('data-active-thread') || '') === String(thread.id);
                var unread = active ? 0 : (parseInt(thread.unread, 10) || 0);
                row.classList.toggle('is-unread', unread > 0);
                var title = row.querySelector('[data-thread-title]');
                if (title) {
                    title.textContent = thread.title || '';
                }
                var badge = row.querySelector('[data-thread-unread]');
                if (badge) {
                    badge.textContent = String(unread);
                    badge.classList.toggle('d-none', unread === 0);
                }
                var preview = row.querySelector('[data-thread-preview]');
                if (preview) {
                    preview.textContent = thread.preview || '';
                }
                var time = row.querySelector('[data-thread-time]');
                if (time && thread.sent_at) {
                    time.setAttribute('data-thread-time', thread.sent_at);
                    time.textContent = humanTime(thread.sent_at);
                }
            }

            function initialsFrom(name) {
                var parts = String(name || '').trim().split(/\s+/).filter(Boolean).slice(0, 2);
                if (!parts.length) {
                    return 'NA';
                }
                return parts.map(function (part) {
                    return part.charAt(0).toUpperCase();
                }).join('');
            }

            function buildRow(thread) {
                var row = document.createElement('a');
                row.href = thread.url;
                row.className = 'customer-messages__thread';
                row.setAttribute('data-thread-id', String(thread.id));
                row.innerHTML = '<span class="customer-messages__avatar" data-thread-initials>' + escapeHtml(initialsFrom(thread.title)) +
                    '</span><span class="customer-messages__meta">' +
                    '<p class="customer-messages__name"><span data-thread-title></span> <span class="badge badge-danger d-none" data-thread-unread>0</span></p>' +
                    '<p class="customer-messages__preview" data-thread-preview></p>' +
                    '<span class="customer-messages__time"><i class="fa fa-clock"></i> <span data-thread-time=""></span></span></span>';
                applyThread(row, thread);
                return row;
            }

            function applyRecent(threads) {
                var box = list.querySelector('.customer-messages__threads');
                if (!box) {
                    return false;
                }
                var empty = box.querySelector('[data-thread-empty]');
                if (empty && threads.length) {
                    empty.remove();
                }
                var changed = false;
                var previous = null;
                threads.forEach(function (thread) {
                    var row = box.querySelector('[data-thread-id="' + thread.id + '"]');
                    if (!row) {
                        row = buildRow(thread);
                        changed = true;
                    } else {
                        var before = (row.querySelector('[data-thread-preview]') || {}).textContent;
                        applyThread(row, thread);
                        if (before !== thread.preview) {
                            changed = true;
                        }
                    }
                    if (previous) {
                        previous.after(row);
                    } else {
                        box.prepend(row);
                    }
                    previous = row;
                });
                return changed;
            }

            function attachmentHtml(message) {
                var file = message.attachment;
                if (!file || !file.url) {
                    return '';
                }
                if (file.is_image) {
                    return '<a href="' + escapeHtml(file.url) + '" target="_blank" class="d-inline-block rounded overflow-hidden border mt-2" style="line-height:0;">' +
                        '<img src="' + escapeHtml(file.url) + '" alt="Attachment" class="img-fluid" style="max-height:140px;max-width:200px;object-fit:cover;"></a>';
                }
                return '<a href="' + escapeHtml(file.url) + '" target="_blank" download class="d-flex align-items-center p-2 mt-2 rounded text-decoration-none ' + (message.mine ? '' : 'bg-light') + '" style="' + (message.mine ? 'background:rgba(255,255,255,0.15);' : '') + '">' +
                    '<span class="mr-2 d-flex align-items-center justify-content-center" style="width:32px;height:32px;border-radius:6px;background:#dfe3ea;color:#3d4d6a;flex:0 0 32px;"><i class="fa fa-file-alt" style="font-size:14px;"></i></span>' +
                    '<span class="text-truncate small ' + (message.mine ? 'text-white' : 'text-dark') + '">' + escapeHtml(file.name || 'Attachment') + '</span></a>';
            }

            function appendLive(messages) {
                var scroll = document.querySelector('.customer-messages__scroll');
                if (!scroll || !messages.length) {
                    return 0;
                }
                var added = 0;
                messages.forEach(function (message) {
                    if (scroll.querySelector('[data-message-id="' + message.id + '"]')) {
                        return;
                    }
                    var mine = !!message.mine;
                    var row = document.createElement('div');
                    row.className = 'd-flex mb-4 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                    row.setAttribute('data-message-id', String(message.id));
                    var body = message.body ? '<p class="mb-0" style="margin:0;line-height:1.45;word-break:break-word;text-align:left;">' + escapeHtml(message.body).replace(/\n/g, '<br>') + '</p>' : '';
                    row.innerHTML = '<div style="max-width:75%;"><div class="p-3 text-left ' + (mine ? 'bg-primary text-white' : 'bg-white border') + '" style="border-radius:' + (mine ? '18px 18px 4px 18px' : '18px 18px 18px 4px') + ';box-shadow:0 2px 8px rgba(0,0,0,0.08);">' +
                        body + attachmentHtml(message) +
                        '</div><small class="text-muted d-block mt-1 px-2" style="font-size:11px;" data-bubble-time="' + escapeHtml(message.sent_at) + '">' + escapeHtml(message.time) + '</small></div>';
                    scroll.appendChild(row);
                    var after = parseInt(scroll.getAttribute('data-after') || '0', 10);
                    if (message.id > after) {
                        scroll.setAttribute('data-after', String(message.id));
                    }
                    added += 1;
                });
                if (added) {
                    scroll.scrollTop = scroll.scrollHeight;
                    scroll.querySelectorAll('img').forEach(function (img) {
                        if (img.complete || img.dataset.scrollFollow === '1') {
                            return;
                        }
                        img.dataset.scrollFollow = '1';
                        img.addEventListener('load', function () {
                            var afterGap = scroll.scrollHeight - scroll.scrollTop - scroll.clientHeight;
                            if (afterGap < 180) {
                                scroll.scrollTop = scroll.scrollHeight;
                            }
                        });
                    });
                }
                return added;
            }

            function scheduleRecent() {
                clearTimeout(recentTimer);
                if (document.hidden) {
                    return;
                }
                recentTimer = setTimeout(pollRecent, recentDelay);
            }

            function scheduleLive() {
                clearTimeout(liveTimer);
                if (document.hidden) {
                    return;
                }
                liveTimer = setTimeout(pollLive, liveDelay);
            }

            function pollRecent() {
                if (recentFlight || document.hidden || !list.getAttribute('data-recent-url')) {
                    return;
                }
                recentFlight = true;
                fetch(list.getAttribute('data-recent-url'), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('recent failed');
                    }
                    return response.json();
                }).then(function (data) {
                    recentDelay = applyRecent((data && data.threads) || []) ? baseMs : Math.min(recentDelay + stepMs, maxMs);
                }).catch(function () {
                    recentDelay = Math.min(recentDelay + stepMs, maxMs);
                }).then(function () {
                    recentFlight = false;
                    scheduleRecent();
                });
            }

            function pollLive() {
                var scroll = document.querySelector('.customer-messages__scroll');
                if (liveFlight || document.hidden || !scroll || !scroll.getAttribute('data-live-url')) {
                    return;
                }
                liveFlight = true;
                fetch(scroll.getAttribute('data-live-url') + '?after=' + (scroll.getAttribute('data-after') || '0'), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('live failed');
                    }
                    return response.json();
                }).then(function (data) {
                    var added = appendLive((data && data.messages) || []);
                    liveDelay = added ? baseMs : Math.min(liveDelay + stepMs, maxMs);
                    if (added) {
                        recentDelay = baseMs;
                        pollRecent();
                    }
                }).catch(function () {
                    liveDelay = Math.min(liveDelay + stepMs, maxMs);
                }).then(function () {
                    liveFlight = false;
                    scheduleLive();
                });
            }

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    clearTimeout(recentTimer);
                    clearTimeout(liveTimer);
                    return;
                }
                recentDelay = baseMs;
                liveDelay = baseMs;
                refreshTimes();
                pollRecent();
                pollLive();
            });

            window.CustomerMessageChat = {
                append: appendLive,
                refreshList: function () {
                    recentDelay = baseMs;
                    pollRecent();
                }
            };

            refreshTimes();
            setInterval(function () {
                if (!document.hidden) {
                    refreshTimes();
                }
            }, 5000);
            scheduleRecent();
            scheduleLive();
        })();
    </script>
</div>
