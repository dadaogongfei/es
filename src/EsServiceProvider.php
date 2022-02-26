<?php
namespace Zyw\Es;
use App\Providers\AppServiceProvider;
use Zyw\Es\EsSearch;
class EsServiceProvider extends AppServiceProvider
{
    public function boot()
    {
        $path = realpath(__DIR__.'/../config/config.php');
        $this->publishes([$path => config_path('es.php')], 'config');
    }
    public function register()
    {
         $this->app->singleton('es',function($app){
             return new EsSearch();
         });
    }
}

