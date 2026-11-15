<?php

use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API (лабораторные работы №6 и №15)
|--------------------------------------------------------------------------
| Сайт остаётся на Blade-шаблонах (routes/web.php), а здесь живёт тот же
| функционал в виде JSON-API: контроллеры в app/Http/Controllers/API
| отвечают не view() и redirect(), а response()->json().
|
| Все адреса получают префикс /api — его добавляет Laravel для этого файла.
|
| Аутентификация:
|   GET  /api/register  — описание полей регистрации
|   POST /api/register  — регистрация, в ответ токен Sanctum
|   POST /api/login     — вход, в ответ токен Sanctum
|   POST /api/logout    — выход: токен удаляется (нужен Authorization: Bearer)
|   GET  /api/user      — текущий пользователь по токену
|
| Новости:
|   GET    /api/articles       — список с пагинацией (?per_page=6)
|   GET    /api/articles/{id}  — одна новость с принятыми комментариями
|   POST   /api/articles       — создать (только модератор)
|   PUT    /api/articles/{id}  — обновить (только модератор)
|   DELETE /api/articles/{id}  — удалить (только модератор)
*/

// throttle:6,1 — защита от перебора пароля: не больше шести попыток в минуту с одного IP.
Route::get('/register', [AuthController::class, 'create']);
Route::post('/register', [AuthController::class, 'registration'])->middleware('throttle:6,1,register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1,login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Проверка токена Sanctum (лабораторная работа №6).
    Route::get('/user', fn (Request $request) => $request->user());
});

/*
 | Новости. apiResource создаёт пять маршрутов (index, store, show, update,
 | destroy) без create/edit — форм в API нет. Запись и удаление закрываем
 | middleware auth:sanctum, а роли проверяют политики ArticlePolicy.
 |
 | names('api.articles') обязателен: без префикса имена вида articles.index
 | перезаписали бы одноимённые веб-маршруты, и redirect()->route('articles.index')
 | уводил бы на /api/articles вместо страницы управления.
 */
Route::apiResource('articles', ArticleController::class)
    ->names('api.articles')
    ->middlewareFor(['store', 'update', 'destroy'], 'auth:sanctum');
