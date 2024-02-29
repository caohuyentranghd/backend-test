<?php

namespace App\Providers;

use App\Services\Repositories\Favorite\FavoriteRepository;
use App\Services\Repositories\Favorite\FavoriteRepositoryInterface;
use App\Services\Repositories\Movie\MovieRepository;
use App\Services\Repositories\Movie\MovieRepositoryInterface;
use App\Services\Repositories\User\UserRepository;
use App\Services\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 *
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
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
        $this->app->singleton(MovieRepositoryInterface::class, MovieRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(FavoriteRepositoryInterface::class, FavoriteRepository::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            MovieRepositoryInterface::class,
            UserRepositoryInterface::class,
            FavoriteRepositoryInterface::class,
        ];
    }
}