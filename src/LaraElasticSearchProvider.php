<?php

namespace DishCheng\LaraLumenElasticSearch;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

class LaraElasticSearchProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/elasticsearch.php'=>config_path('elasticsearch.php'),
            ]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('elasticsearch');
        }

//        if ($this->app->runningInConsole()) {
//            $this->commands([
//            ]);
//        }
    }


    public function register()
    {

    }
}