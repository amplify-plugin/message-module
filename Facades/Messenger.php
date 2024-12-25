<?php

namespace Amplify\System\Message\Facades;

use Amplify\System\Message\Interfaces\MessageInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;

/**
 * @method static self attachment(?UploadedFile $attachment)
 * @method static Model|MessageInterface|null send()
 * @method static self to(Model $model)
 * @method static self from(Model $model)
 * @method static self message(string $message)
 */
class Messenger extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'messenger';
    }
}
