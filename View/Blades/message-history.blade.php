<div class="chat-header clearfix">
    <div class="row">
        <div class="col-lg-12">
            <a href="javascript:void(0);" data-toggle="modal" data-target="#view_info">
                <img src="{{ $theadImage()}}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover"
                     alt="avatar">
            </a>
            <div class="chat-about">
                <h6 class="mt-2">{{ $threadTitle() }}</h6>
            </div>
            <div class="chat-toggle-icon d-md-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="#1b2a4e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-menu">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>

                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                     stroke="red" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="feather feather-x">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>

            </div>
        </div>
    </div>
</div>

<div class="chat-history py-0 pr-0">
    <ul class="mb-0 pl-0" @if(empty($messages)) style="height: 480px" @endif>
        @foreach ($messages as $message)
            <li class="clearfix my-2">
                <div
                    class="message @if ($message->sender_id === $reader_user->id) other-message float-right @else my-message @endif">
                    @if ($message->attachment)
                        @if (in_array($message->attachment_extension, ['jpg', 'jpeg', 'png']))
                            <a href="{{ $message->attachment }}" data-lightbox="{{ $message->attachment }}"
                               data-title="{{ $message->body }}"
                               title="{{ $message->attachment_title ?? $message->attachment_name }}">
                                <object class="img-fluid" data="{{ $message->attachment }}"></object>
                            </a>
                        @else
                            <a href="{{ $message->attachment }}" download title="Download Attachment"
                               class="d-flex justify-content-start mt-3">
                                <div class="media border p-1 rounded" style="background-color: #EFEFEF;">
                                    <i class="rounded-circle align-self-center mr-1 text-dark"
                                       style="background-color: #d1d1d1;width: 40px; height: 40px; padding: 7px 13px 8px 6px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 384 512">
                                            <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                            <path
                                                d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm160-14.1v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z"/>
                                        </svg>
                                    </i>
                                    <div class="media-body pt-2">
                                        <p class="align-self-center"
                                           style="word-break: break-all;"> {{ $message->attachment_title ?? $message->attachment_name }}</p>
                                    </div>
                                </div>
                            </a>
                        @endif
                    @endif
                    <p class="text-left">{!! nl2br($message->body) !!}</p>
                    <small
                        class="message-data-time text-muted font-italic">{{ $message->created_at->diffForHumans() }}
                    </small>
                </div>
            </li>
        @endforeach
    </ul>
</div>

@if($hasMessagingPermission())
    <div class="chat-message clearfix">
        <form
            action="{{ $threadLink() }}"
            method="post" enctype="multipart/form-data">
            <input type="hidden" name="as_customer" value="{{ $as_customer }}"/>
            @csrf
            @if ($thread)
                @method("PUT")
            @endif

            @if (!isset($thread->id))
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>User type<span class="text-danger">*</span></label>
                        <select name="user_type" class="form-control custom-select"
                                onchange="changeMessageAbleUser(this.value);" id="user_type" oninput="checkTextArea();">
                            <option value="">Select one</option>
                            <option value="user" title="Admin panel users">User</option>
                            <option value="contact" title="Customer contact accounts">Contact</option>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label>Message to<span class="text-danger">*</span></label>
                        <select name="msg_to" class="form-control custom-select" id="messageableUser" required disabled oninput="checkTextArea();">
                            <option value="">Select User Type First</option>
                        </select>
                    </div>
                </div>
            @endif

            <div class="form-group">
                <div class="input-group">
                <textarea name="msg" id="message" oninput="checkTextArea();" class="form-control"
                          style="padding-left: 0.75rem; height: 75px" rows="2"
                          placeholder="Enter text here..."></textarea>
                    <div class="input-group-append">
                        <button
                            type="submit"
                            class="btn btn-info rounded-right my-0 px-2 frontend-message-save-btn input-group-btn"
                            id="send-msg"
                            disabled
                            @if (!isset($thread->id)) disabled @endif
                        >➤
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="frontend-message-fileupload input-group clone-field">
                    <div class="custom-file">
                        <input type="file" class="form-control custom-file-input" oninput="checkTextArea();"
                               name="attachment" id="attachments">
                        <label class="custom-file-label" for="upload-file">Choose file</label>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endif
<script>

    function checkTextArea() {
        var messageTextarea = document.getElementById('message');
        var attachments = document.getElementById('attachments');
        var sendButton = document.getElementById('send-msg');
        var messageableUser = document.getElementById('messageableUser');
        var user_type = document.getElementById('user_type');

        if (messageableUser.value.trim() !== '' && user_type.value.trim() !== '' && messageTextarea.value.trim() !== '' || attachments.value.trim() !== '') {

            sendButton.disabled = false;
        } else {
            sendButton.disabled = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        $(document).on('change', '.custom-file-input', function () {
            let filename = $(this).val()
            $(this).next('.custom-file-label').text(filename)
        });
    });

    document.querySelector('.chat-toggle-icon').addEventListener('click', (e) => {
        document.querySelector('.chat-toggle-icon').classList.toggle('show')
        document.querySelector('.people-list').classList.toggle('show')
    });

    function changeMessageAbleUser(value) {
        var select = $("#messageableUser");
        select.empty();
        select.prop("disabled", true);
        if (value.length > 0) {
            var url = ("{{ route('messages.recipients', '##') }}").replace("##", value).toString();
            $.get(url, {
                'as_customer': '{{ $as_customer ? 'true' : 'false' }}'
            }, function (response) {
                // $('#send-msg').prop("disabled", false);

                if (response.type === 'user') {
                    $.each(response.account, function (index, account) {
                        select.append(`<option value='${account.id}'>${account.name}`);
                    });
                } else if (response.type === 'contact') {
                    $.each(response.account, function (index, accountGroup) {
                        let optgroup = $(`<optgroup label='${accountGroup.customer_name}'></optgroup>`);

                        $.each(accountGroup.contacts, function (index, account) {
                            optgroup.append(`<option value='${account.id}'>${account.name}</option>`);
                        });

                        select.append(optgroup);
                    });
                }
                select.prop("disabled", false);
            });
        } else {
            $('#send-msg').prop("disabled", true);
            select.append("<option value=''>Select User Type First</option>");
        }
    }
</script>
