<?php

namespace App\Core\Providers;

use App\Core\Rest;
use Illuminate\Support\ServiceProvider;

class RestProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('rest',function(){
            return new Rest();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
