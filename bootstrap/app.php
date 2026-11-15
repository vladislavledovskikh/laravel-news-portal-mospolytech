<?php

use App\Http\Middleware\TrackArticleView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         | Лабораторная работа №14: посредник учёта просмотров новостей.
         |
         | В Laravel 13 нет app/Http/Kernel.php — посредники регистрируются
         | здесь, в bootstrap/app.php. Даём классу короткое имя 'track.view',
         | чтобы навесить его на нужный маршрут в routes/web.php.
         */
        $middleware->alias([
            'track.view' => TrackArticleView::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /*
         | Свой ответ на отказ в доступе. Текст берём из политики/шлюза
         | (Response::deny('...')).
         |
         | Laravel превращает AuthorizationException в AccessDeniedHttpException
         | ещё до вызова наших обработчиков, поэтому ловим именно её —
         | текст отказа при этом сохраняется в сообщении исключения.
         |
         | Для GET отдаём страницу errors/403 (её отрисует стандартный
         | обработчик, передав в шаблон $exception), для отправки формы —
         | возвращаем назад с сообщением в flash.
         */
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            $text = $e->getMessage() ?: 'Недостаточно прав для этого действия.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $text,
                ], 403);
            }

            if ($request->isMethod('GET')) {
                return null; // отрисуется resources/views/errors/403.blade.php
            }

            return back()->with('error', $text);
        });
    })->create();
