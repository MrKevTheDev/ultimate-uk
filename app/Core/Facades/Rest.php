<?php

namespace App\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Core\Rest
 */
class Rest extends Facade {
    protected static function getFacadeAccessor()
    {
        return 'rest';
    }
}
