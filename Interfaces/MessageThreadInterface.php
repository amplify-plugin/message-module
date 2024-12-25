<?php

namespace Amplify\System\Message\Interfaces;

interface MessageThreadInterface
{
    public function messages();

    public function participants();

    public function getSenderAttribute();

    public function getLastMessageAttribute();

    public function getUnreadMessagesCountAttribute();

    public function getCreatorAttribute();

    public function scopeBetween($query, $participants);
}
