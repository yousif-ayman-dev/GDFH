<?php

namespace App\Providers;

use App\Services\AI\AIProviderInterface;
use App\Services\AI\GeminiAIProvider;
use App\Services\AI\RuleBasedAIProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, function ($app) {
            $apiKey = config('services.gemini.api_key');
            $fallback = $app->make(RuleBasedAIProvider::class);

            if (! empty($apiKey)) {
                return new GeminiAIProvider($fallback);
            }

            return $fallback;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
