@php
    use Amplify\System\Backend\Models\Contact;
    use Amplify\System\Backend\Models\User;
    $contact = customer(true);
@endphp
<div {!! $htmlAttributes !!}>
    <div class="card border-0 shadow">
        <div class="customer-messages">
            <aside class="customer-messages__list"
                   data-recent-url="{{ route('frontend.messages.recent') }}"
                   data-active-thread="{{ $current->id ?? '' }}">
                <div class="customer-messages__toolbar">
                    <a href="{{ route('frontend.messages.index') }}" class="btn btn-primary customer-messages__new">
                        <i class="fa fa-edit"></i> New message
                    </a>
                </div>
                <h4 class="customer-messages__heading">Messages</h4>
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
                                <textarea name="msg" class="form-control border ticket-composer__input" rows="1" placeholder="Enter text here...">{{ old('msg') }}</textarea>
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
                            <input type="file" name="attachment" class="chat-file-input" data-file-input tabindex="-1" aria-hidden="true" accept="{{ \Amplify\System\Message\Http\Requests\MessageRequest::acceptAttribute() }}">
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
                    <form class="customer-messages__compose"
                          action="{{ route('frontend.messages.store') }}"
                          method="post"
                          enctype="multipart/form-data"
                          data-recipients-user="{{ route('frontend.messages.recipients', ['type' => 'user']) }}"
                          data-recipients-contact="{{ route('frontend.messages.recipients', ['type' => 'contact']) }}">
                        @csrf
                        <input type="hidden" name="as_customer" value="1">
                        <header class="px-4 py-3 border-bottom bg-white">
                            <h6 class="mb-0 font-weight-bold text-dark">New message</h6>
                        </header>
                        <div class="customer-messages__compose-body"></div>
                        <footer class="border-top bg-white p-3">
                            <div class="customer-messages__compose-fields">
                                <div class="customer-messages__field">
                                    <label for="customer-message-user-type">User type<span class="text-danger">*</span></label>
                                    <select name="user_type" id="customer-message-user-type" class="form-control" data-user-type>
                                        <option value="">Select one</option>
                                        <option value="user" @selected(old('user_type') === 'user')>Admin</option>
                                        <option value="contact" @selected(old('user_type') === 'contact')>Contact</option>
                                    </select>
                                    @error('user_type')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="customer-messages__field customer-messages__field--to">
                                    <label for="customer-message-recipient">Message to<span class="text-danger">*</span></label>
                                    <div class="customer-messages__recipient">
                                        <input type="text" id="customer-message-recipient" class="form-control" data-recipient-search placeholder="Select user type first" autocomplete="off" disabled>
                                        <input type="hidden" name="msg_to" value="{{ old('msg_to') }}" data-recipient-id>
                                        <div class="customer-messages__recipient-results" data-recipient-results hidden></div>
                                    </div>
                                    @error('msg_to')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <small class="text-muted d-block customer-messages__hint">Admins are internal users. Contacts are people on your account.</small>
                            <div class="message-file-pick" data-file-chip hidden>
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
                                <textarea name="msg" class="form-control border ticket-composer__input" rows="1" placeholder="Enter text here...">{{ old('msg') }}</textarea>
                                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center ticket-composer__btn" data-send disabled aria-label="Send">
                                    <i class="fa fa-paper-plane" data-send-icon></i>
                                    <span class="chat-spinner" data-send-spinner hidden></span>
                                </button>
                            </div>
                            @error('msg')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            @error('attachment')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            <input type="file" name="attachment" class="chat-file-input" data-file-input tabindex="-1" aria-hidden="true" accept="{{ \Amplify\System\Message\Http\Requests\MessageRequest::acceptAttribute() }}">
                        </footer>
                    </form>
                    <script>
                        (function () {
                            var form = document.querySelector('.customer-messages__compose');
                            if (!form) {
                                return;
                            }
                            var type = form.querySelector('[data-user-type]');
                            var search = form.querySelector('[data-recipient-search]');
                            var hidden = form.querySelector('[data-recipient-id]');
                            var results = form.querySelector('[data-recipient-results]');
                            var input = form.querySelector('[data-file-input]');
                            var open = form.querySelector('[data-file-open]');
                            var chip = form.querySelector('[data-file-chip]');
                            var name = form.querySelector('[data-file-name]');
                            var size = form.querySelector('[data-file-size]');
                            var preview = form.querySelector('[data-file-preview]');
                            var fileIcon = form.querySelector('[data-file-icon]');
                            var clear = form.querySelector('[data-file-clear]');
                            var text = form.querySelector('[name="msg"]');
                            var send = form.querySelector('[data-send]');
                            var sendIcon = form.querySelector('[data-send-icon]');
                            var spinner = form.querySelector('[data-send-spinner]');
                            var choices = [];
                            var previewUrl = '';
                            var selectedId = hidden ? hidden.value : '';

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

                            function refreshSend() {
                                var hasBody = (text && text.value.trim() !== '') || (input.files && input.files.length > 0);
                                var hasRecipient = hidden && hidden.value.trim() !== '' && type && type.value.trim() !== '';
                                send.disabled = form.dataset.sending === '1' || !hasBody || !hasRecipient;
                            }

                            function showNote(message) {
                                results.hidden = false;
                                results.innerHTML = '';
                                var note = document.createElement('div');
                                note.className = 'customer-messages__recipient-empty';
                                note.textContent = message;
                                results.appendChild(note);
                            }

                            function renderChoices(items) {
                                results.innerHTML = '';
                                if (!items.length) {
                                    showNote('No matches');
                                    return;
                                }
                                items.forEach(function (item) {
                                    var button = document.createElement('button');
                                    button.type = 'button';
                                    button.textContent = item.text;
                                    button.addEventListener('click', function () {
                                        hidden.value = String(item.id);
                                        search.value = item.text;
                                        results.hidden = true;
                                        refreshSend();
                                    });
                                    results.appendChild(button);
                                });
                                results.hidden = false;
                            }

                            function filteredChoices() {
                                var term = search.value.trim().toLowerCase();
                                if (!term) {
                                    return choices;
                                }
                                return choices.filter(function (item) {
                                    return String(item.text || '').toLowerCase().indexOf(term) !== -1;
                                });
                            }

                            function loadRecipients(kind, done) {
                                var url = kind === 'contact' ? form.getAttribute('data-recipients-contact') : form.getAttribute('data-recipients-user');
                                choices = [];
                                search.disabled = true;
                                search.placeholder = 'Loading...';
                                fetch(url, {
                                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                    credentials: 'same-origin'
                                }).then(function (response) {
                                    if (!response.ok) {
                                        throw new Error('recipients failed');
                                    }
                                    return response.json();
                                }).then(function (data) {
                                    if (type.value !== kind) {
                                        return;
                                    }
                                    choices = (data && data.results) || [];
                                    search.disabled = false;
                                    search.placeholder = kind === 'contact' ? 'Search contacts on your account' : 'Search admins';
                                    if (done) {
                                        done();
                                    }
                                }).catch(function () {
                                    if (type.value !== kind) {
                                        return;
                                    }
                                    search.disabled = false;
                                    search.placeholder = 'Could not load names';
                                    showNote('Could not load names');
                                });
                            }

                            type.addEventListener('change', function () {
                                hidden.value = '';
                                search.value = '';
                                results.hidden = true;
                                choices = [];
                                refreshSend();
                                if (!type.value) {
                                    search.disabled = true;
                                    search.placeholder = 'Select user type first';
                                    return;
                                }
                                loadRecipients(type.value);
                            });

                            search.addEventListener('input', function () {
                                if (hidden.value) {
                                    hidden.value = '';
                                    refreshSend();
                                }
                                if (!type.value) {
                                    return;
                                }
                                renderChoices(filteredChoices());
                            });
                            search.addEventListener('focus', function () {
                                if (type.value && !search.disabled) {
                                    renderChoices(filteredChoices());
                                }
                            });
                            document.addEventListener('click', function (event) {
                                if (!form.contains(event.target)) {
                                    results.hidden = true;
                                }
                            });

                            open.addEventListener('click', function () {
                                input.click();
                            });
                            input.addEventListener('change', function () {
                                var file = input.files && input.files[0];
                                clearPreview();
                                if (!file) {
                                    chip.hidden = true;
                                    name.textContent = '';
                                    size.textContent = '';
                                    refreshSend();
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
                                refreshSend();
                            });
                            clear.addEventListener('click', function () {
                                input.value = '';
                                chip.hidden = true;
                                name.textContent = '';
                                size.textContent = '';
                                clearPreview();
                                refreshSend();
                            });
                            text.addEventListener('input', refreshSend);

                            form.addEventListener('submit', function (event) {
                                refreshSend();
                                if (send.disabled || form.dataset.sending === '1') {
                                    event.preventDefault();
                                    return;
                                }
                                form.dataset.sending = '1';
                                send.disabled = true;
                                if (sendIcon) {
                                    sendIcon.hidden = true;
                                }
                                if (spinner) {
                                    spinner.hidden = false;
                                }
                            });

                            if (type.value) {
                                loadRecipients(type.value, function () {
                                    var match = choices.find(function (item) {
                                        return String(item.id) === String(selectedId);
                                    });
                                    if (match) {
                                        hidden.value = String(match.id);
                                        search.value = match.text;
                                    }
                                    refreshSend();
                                });
                            }
                            refreshSend();
                        })();
                    </script>
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
