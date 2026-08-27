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
        if (
            $this->app->environment('production') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Fail-safe DB connection check
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            if (\Illuminate\Support\Facades::Schema::hasTable('migrations')) {
                // DB is accessible
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Primary DB connection failed: ' . $e->getMessage() . '. Falling back to SQLite.');
            config(['database.default' => 'sqlite']);
            \Illuminate\Support\Facades\DB::purge();
        }
    }
}
