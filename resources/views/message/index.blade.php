<div {!! $htmlAttributes !!}>
    <div class="card chat-app">
        <div id="plist" class="people-list" style="padding-top: 0.6rem !important;">
            @if (customer(true)->can('message.messaging'))
                <div class="text-right">
                    <a href="{{ route('frontend.messages.index') }}" class="btn btn-outline-warning btn-block mt-0">
                        <i class="icon-mail"></i> New message</a>
                </div>
                <x-message-profile :as-customer="true" :threads="$threads" :current="$threadMsg" />
            @endif
        </div>

        <div class="chat">
            <x-message-history :as-customer="true" :current="$threadMsg" />
        </div>
    </div>
</div>
