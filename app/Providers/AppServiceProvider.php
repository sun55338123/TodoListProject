<?php

namespace App\Providers;

use App\Contracts\Repositories\DailyTaskRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\WeeklyGoalRepositoryInterface;
use App\Contracts\Services\AuthenticatorInterface;
use App\Contracts\Services\DailyTaskServiceInterface;
use App\Contracts\Services\UserRegistrationServiceInterface;
use App\Contracts\Services\WeeklyGoalServiceInterface;
use App\Repositories\EloquentDailyTaskRepository;
use App\Repositories\EloquentUserRepository;
use App\Repositories\EloquentWeeklyGoalRepository;
use App\Services\DailyTaskService;
use App\Services\SessionAuthenticator;
use App\Services\UserRegistrationService;
use App\Services\WeeklyGoalService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 介面與實作的綁定。Controller 依賴 Service 介面，Service 依賴 Repository 介面。
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        // Repositories
        UserRepositoryInterface::class => EloquentUserRepository::class,
        DailyTaskRepositoryInterface::class => EloquentDailyTaskRepository::class,
        WeeklyGoalRepositoryInterface::class => EloquentWeeklyGoalRepository::class,

        // Services
        AuthenticatorInterface::class => SessionAuthenticator::class,
        UserRegistrationServiceInterface::class => UserRegistrationService::class,
        DailyTaskServiceInterface::class => DailyTaskService::class,
        WeeklyGoalServiceInterface::class => WeeklyGoalService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
