<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Pagination\Paginator;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Discord\DiscordExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
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
        Paginator::defaultView('vendor.pagination.default');

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Discord\Provider::class);
        });

        // Auto-purge Cloudflare cache when wiki content changes
        \App\Models\WikiArticle::saved(function (\App\Models\WikiArticle $article) {
            \Illuminate\Support\Facades\Artisan::queue('cf:purge', [
                '--url' => [
                    rtrim(config('services.cloudflare.base_url'), '/') . '/wiki.html',
                    rtrim(config('services.cloudflare.base_url'), '/') . '/wiki:' . $article->slug . '.html',
                ],
            ]);
        });

        \App\Models\WikiArticle::deleted(function (\App\Models\WikiArticle $article) {
            \Illuminate\Support\Facades\Artisan::queue('cf:purge', [
                '--url' => [
                    rtrim(config('services.cloudflare.base_url'), '/') . '/wiki.html',
                    rtrim(config('services.cloudflare.base_url'), '/') . '/wiki:' . $article->slug . '.html',
                ],
            ]);
        });
    }
}
