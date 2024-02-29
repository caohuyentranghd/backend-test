<?php

namespace App\Providers;

use App\Services\Internals\Favorite\FavoriteService;
use App\Services\Internals\Favorite\FavoriteServiceInterface;
use App\Services\Internals\Movie\MovieService;
use App\Services\Internals\Movie\MovieServiceInterface;
use App\Services\Internals\User\UserService;
use App\Services\Internals\User\UserServiceInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Class InternalServiceProvider
 *
 * @package App\Providers
 */
class InternalServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MovieServiceInterface::class, MovieService::class);
        $this->app->singleton(UserServiceInterface::class, UserService::class);
        $this->app->singleton(FavoriteServiceInterface::class, FavoriteService::class);
    }

     /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            MovieServiceInterface::class,
            UserServiceInterface::class,
            FavoriteServiceInterface::class,
        ];
    }
}