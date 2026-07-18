<?php

namespace xcesaralejandro\canvasoauth\Providers;

use Illuminate\Support\ServiceProvider;
use xcesaralejandro\canvasoauth\Console\Commands\CreateCanvasClient;

class CanvasOauthServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadRoutesFrom($this->packageBasePath('routes/web.php'));
        $this->publishes([
            $this->packageBasePath('database/migrations') => database_path('migrations')
        ], 'xcesaralejandro-canvasoauth-migrations');
        $this->publishes([
            $this->packageBasePath('src/Models/publish') => base_path('app/Models')
        ], 'xcesaralejandro-canvasoauth-models');
        $this->publishes([
            $this->packageBasePath('Http/Controllers/publish') => base_path('app/Http/Controllers')
        ], 'xcesaralejandro-canvasoauth-controller');
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateCanvasClient::class,
            ]);
        }
    }

    protected function packageBasePath($uri)
    {
        return __DIR__ . "/../../$uri";
    }
}
