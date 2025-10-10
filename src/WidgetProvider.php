<?php

namespace Amplify\System\Message;


use Amplify\System\Message\Widgets\History;
use Amplify\System\Message\Widgets\Index;
use Amplify\System\Message\Widgets\Profile;
use Amplify\Widget\Abstracts\Widget;
use Illuminate\Support\ServiceProvider;

class WidgetProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $widgets = [
            History::class => [
                'name' => 'message-history',
                'reserved' => true,
                'internal' => true,
                '@inside' => null,
                '@client' => null,
                'model' => ['shop'],
                '@attributes' => [
                    ['name' => ':as-customer', 'type' => 'boolean', 'value' => false],
                    ['name' => ':current', 'type' => 'text', 'value' => 'null'],
                ],
                '@nestedItems' => [],
                'description' => 'Product shop empty result widget',
            ],
            Profile::class => [
                'name' => 'message-profile',
                'reserved' => true,
                'internal' => true,
                'model' => ['static_page'],
                '@inside' => null,
                '@client' => null,
                '@attributes' => [
                    ['name' => ':as-customer', 'type' => 'boolean', 'value' => false],
                    ['name' => ':current', 'type' => 'text', 'value' => 'null'],
                    ['name' => ':threads', 'type' => 'text', 'value' => 'null'],
                ],
                '@nestedItems' => [],
                'description' => 'EasyAsk Shop Pagination widget',
            ],
            Index::class => [
                'name' => 'customer.message',
                'reserved' => true,
                'internal' => true,
                'model' => ['message'],
                '@inside' => null,
                '@client' => null,
                '@attributes' => [],
                '@nestedItems' => [],
                'description' => 'EasyAsk Shop Pagination widget',
            ],
        ];

        foreach ($widgets as $namespace => $options) {
            Widget::register($namespace, $options['name'], $options);
        }
    }
}
