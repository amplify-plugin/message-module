<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    */

    'models' => [
        'message' => \Amplify\System\Message\Models\Message::class,
        'thread' => \Amplify\System\Message\Models\MessageThread::class,
        'participant' => \Amplify\System\Message\Models\MessageThreadParticipant::class,
    ],

];
