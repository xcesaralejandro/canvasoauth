<?php 
namespace xcesaralejandro\canvasoauth\Providers;

use Illuminate\Support\ServiceProvider;
use xcesaralejandro\canvasoauth\Classes\CanvasOauth;

class CanvasOauthServiceProvider extends ServiceProvider {

    public function boot(){
        $this->loadRoutesFrom($this->packageBasePath('routes/web.php'));
        $this->publishes([
            $this->packageBasePath('config/canvasoauth.php') => base_path("config/canvasoauth.php")
        ], 'xcesaralejandro-canvasoauth-config');
        $this->publishes([
            $this->packageBasePath('database/migrations') => database_path('migrations')
        ], 'xcesaralejandro-canvasoauth-migrations');
        $this->publishes([
            $this->packageBasePath('src/Models/publish') => base_path('app/Models')
        ], 'xcesaralejandro-canvasoauth-models');
    }

    public function register(){
        $this->app->bind('canvas-oauth', function(){
            return new CanvasOauth();
        });
        $this->mergeConfigFrom($this->packageBasePath('config/canvasoauth.php'), "canvasoauth");
    }

    protected function packageBasePath($uri){
        return __DIR__."/../../$uri";
    }
}