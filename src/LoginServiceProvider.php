<?php

namespace Andruby\Login;

use Andruby\Login\Console\InstallCommand;
use Andruby\Login\Middleware\WeixinWebLogin;
use Illuminate\Support\ServiceProvider;

class LoginServiceProvider extends ServiceProvider
{

    protected $routeMiddleware = [
        'login.weixin.web' => WeixinWebLogin::class
    ];

    protected $commands = [
        InstallCommand::class
    ];

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->publishes([
                __DIR__ . '/../config/deep_login.php' => config_path('deep_login.php'),
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/route.php');

    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/deep_login.php', 'deep_login');

        $this->registerRouteMiddleware();

        $this->commands($this->commands);
    }


    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

    }
}
