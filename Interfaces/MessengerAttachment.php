<?php

namespace Amplify\System\Message\Interfaces;

use Illuminate\Http\UploadedFile;

interface MessengerAttachment
{
    public function attachment(?UploadedFile $request);
}
