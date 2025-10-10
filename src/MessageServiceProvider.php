<?php

namespace Amplify\System\Message;

use Amplify\System\Message\Interfaces\MessageInterface;
use Amplify\System\Message\Interfaces\MessageThreadInterface;
use Amplify\System\Message\Interfaces\MessageThreadParticipantInterface;
use Amplify\System\Message\Widgets\History;
use Amplify\System\Message\Widgets\Profile;
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
        $this->mergeConfigFrom(__DIR__.'/../config/messenger.php', 'messenger');

        $this->app->singleton('messenger', function () {
            return new Messenger;
        });

        $this->app->bind(MessageInterface::class, config('messenger.models.message'));
        $this->app->bind(MessageThreadInterface::class, config('messenger.models.thread'));
        $this->app->bind(MessageThreadParticipantInterface::class, config('messenger.models.participant'));

        $this->app->register(WidgetProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/message.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'message');

    }
}
