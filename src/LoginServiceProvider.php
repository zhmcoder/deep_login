<?php

namespace Andruby\Login;

use Andruby\Login\Console\InstallCommand;
use Andruby\Login\Middleware\ImgCode;
use Andruby\Login\Middleware\WeixinWebLogin;
use Illuminate\Support\ServiceProvider;

class LoginServiceProvider extends ServiceProvider
{

    protected $routeMiddleware = [
        'login.weixin.web' => WeixinWebLogin::class,
        'imgCode' => ImgCode::class
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'deep-login');

        $this->registerPublishing();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/deep_login.php', 'deep_login');

        $this->registerRouteMiddleware();

        $this->commands($this->commands);
    }

    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../resources/views' => resource_path('views')], 'deep-login-views');
        }
    }

    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

    }
}
