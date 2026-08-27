<?php

namespace App\Providers;

use App\Services\AI\AIProviderInterface;
use App\Services\AI\GeminiAIProvider;
use App\Services\AI\OpenAIAIProvider;
use App\Services\AI\RuleBasedAIProvider;
use App\Services\AI\MultiProviderAIAdapter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, function ($app) {
            $ruleBased = $app->make(RuleBasedAIProvider::class);
            $gemini = new GeminiAIProvider($ruleBased);
            $openAI = new OpenAIAIProvider($ruleBased);

            return new MultiProviderAIAdapter($gemini, $openAI, $ruleBased);
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
