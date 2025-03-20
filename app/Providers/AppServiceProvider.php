<?php

namespace App\Providers;

use App\Repositories\Interfaces\RecommendationRepositoryInterface;
use App\Repositories\RecommendationRepository;
use App\Services\Auth\AuthService;
use App\Services\Auth\IAuthService;
use App\Services\Profile\IProfileService;
use App\Services\Profile\ProfileService;
use App\Services\Recommendation\IRecommendationService;
use App\Services\Recommendation\RecommendationService;
use App\Services\Song\ISongService;
use App\Services\Song\SongService;
use App\Services\User\IUserService;
use App\Services\User\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IAuthService::class, AuthService::class);
        $this->app->bind(IProfileService::class, ProfileService::class);
        $this->app->bind(ISongService::class, SongService::class);
        $this->app->bind(IUserService::class, UserService::class);
        $this->app->bind(IRecommendationService::class, RecommendationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
