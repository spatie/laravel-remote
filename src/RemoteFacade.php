<?php

namespace Spatie\Remote;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\Remote\Remote
 */
class RemoteFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-remote';
    }
}
