<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LoginRepository;
use App\Repositories\AboutusRepository;
use App\Repositories\CommentRepository;
use App\Repositories\HomeRepository;
use App\Repositories\NodeRepository;
use App\Repositories\RegisterRepository;
use App\Repositories\Interfaces\LoginInterface;
use App\Repositories\Interfaces\HomeInterface;
use App\Repositories\Interfaces\NodeInterface;
use App\Repositories\Interfaces\RegisterInterface;
use App\Repositories\Interfaces\AboutusInterface;
use App\Repositories\Interfaces\CommentInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginInterface::class, LoginRepository::class);
        $this->app->bind(NodeInterface::class, NodeRepository::class);
        $this->app->bind(AboutusInterface::class, AboutusRepository::class);
        $this->app->bind(HomeInterface::class, HomeRepository::class);
        $this->app->bind(RegisterInterface::class, RegisterRepository::class);
        $this->app->bind(CommentInterface::class, CommentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->useLangPath(base_path('lang'));
    }
}
