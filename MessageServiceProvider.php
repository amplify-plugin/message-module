<?php

namespace Amplify\System\Message;

use Amplify\System\Message\Interfaces\MessageInterface;
use Amplify\System\Message\Interfaces\MessageThreadInterface;
use Amplify\System\Message\Interfaces\MessageThreadParticipantInterface;
use Amplify\System\Message\View\Components\MessageHistory;
use Amplify\System\Message\View\Components\MessageProfile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class MessageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/Config/messenger.php', 'messenger');

        $this->app->singleton('messenger', function () {
            return new Messenger;
        });

        $this->app->bind(MessageInterface::class, config('messenger.models.message'));
        $this->app->bind(MessageThreadInterface::class, config('messenger.models.thread'));
        $this->app->bind(MessageThreadParticipantInterface::class, config('messenger.models.participant'));

        $this->loadRoutesFrom(__DIR__.'/Routes/message.php');

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/View/Blades', 'message');

        Blade::component('message-profile', MessageProfile::class);

        Blade::component('message-history', MessageHistory::class);
    }
}
