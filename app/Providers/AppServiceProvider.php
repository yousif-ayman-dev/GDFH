<?php

namespace App\Providers;

use App\Services\AI\AIProviderInterface;
use App\Services\AI\RuleBasedAIProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, RuleBasedAIProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
