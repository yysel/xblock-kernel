<?php

namespace XBlock\Kernel;

use Illuminate\Support\ServiceProvider;
use XBlock\Kernel\Helper\TemplateCmd;

class KernelProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */


    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources' => base_path('/public/'),
            ], 'public');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->configure('xblock');
        $this->registerMigrations();
        $this->app->router->group(['prefix' => config('xblock.prefix', 'api/xblock'), 'namespace' => 'XBlock\Kernel', 'middleware' => config('xblock.middleware', 'auth:api')], function ($router) {
            $router->post('/menu', 'KernelController@menu');
            $router->post('/notification', 'KernelController@notification');
            $router->post('/{block}/{action}', 'BlockController@action');
        });

        $this->commands([
            TemplateCmd::class
        ]);
        $this->app->singleton('field_dict', function () {
            $register = config('xblock.register.dict', false);
            return $register ? new $register : null;
        });
    }

    protected function registerMigrations()
    {
        return $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }


}
