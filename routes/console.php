<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Планировщик задач (лабораторная работа №14)
|--------------------------------------------------------------------------
| В Laravel 13 расписание задаётся не в app/Console/Kernel.php (его больше
| нет), а здесь — в routes/console.php. Сам планировщик запускается системой
| по cron раз в минуту командой `php artisan schedule:run`, а он уже
| проверяет, какие задачи пора выполнить.
|
| stats:daily — письмо модераторам со статистикой сайта за день
| (просмотры новостей и новые комментарии). Для наглядности задача
| выполняется каждую минуту.
*/
Schedule::command('stats:daily')->everyMinute();
