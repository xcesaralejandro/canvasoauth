<?php 
    namespace xcesaralejandro\canvas_oauth_for_laravel\Providers;

    use Illuminate\Support\ServiceProvider;

    class CanvasOauthServiceProvider extends ServiceProvider {

        public function boot(){
            $this->loadRoutesFrom($this->packageBasePath('routes/web.php'));
            $this->loadRoutesFrom($this->packageBasePath('routes/api.php'));
        }

        protected function packageBasePath($uri){
            return __DIR__."/../../$uri";
        }
    }