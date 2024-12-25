<?php

namespace Amplify\System\Message\Interfaces;

interface MessageInterface
{
    public function sender();

    public function thread();

    public function scopeFromSender($query, $sender);
}
