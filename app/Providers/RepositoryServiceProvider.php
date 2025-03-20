<?php

namespace App\Providers;

use App\Repositories\Interfaces\RecommendationRepositoryInterface;
use App\Repositories\Interfaces\SongRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\RecommendationRepository;
use App\Repositories\SongRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(SongRepositoryInterface::class, SongRepository::class);
        $this->app->bind(RecommendationRepositoryInterface::class, RecommendationRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
