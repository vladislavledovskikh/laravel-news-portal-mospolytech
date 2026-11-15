<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        /*
         | Уведомления читателей (лабораторная работа №12).
         | Шапка сайта есть на каждой странице, поэтому непрочитанные
         | уведомления отдаём в layout через composers — один раз,
         | без запросов из самого шаблона.
         */
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            $view->with(
                'unreadNotifications',
                $user ? $user->unreadNotifications()->limit(10)->get() : collect()
            );
        });
    }
}
