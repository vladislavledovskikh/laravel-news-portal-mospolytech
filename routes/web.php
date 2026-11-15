<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Маршруты сайта
|--------------------------------------------------------------------------
| Лабораторная работа №1: маршрутизатор (Route) + шаблонизатор Blade.
| Лабораторная работа №2: контроллер + данные из json-файла.
| Лабораторная работа №4: новости хранятся в БД (модель Article).
| Лабораторная работа №5: пагинация и CRUD новостей.
|
|   GET /                    — главная: список новостей с пагинацией
|   GET /news/{article}      — одна новость: полное изображение и текст
|   GET /articles            — управление новостями (таблица)
|   GET /articles/create     — форма создания
|   POST /articles           — сохранить новую новость
|   GET /articles/{id}/edit  — форма редактирования
|   PUT /articles/{id}       — обновить новость
|   DELETE /articles/{id}    — удалить новость
|   GET /about               — страница «О нас»
|   GET /contacts            — страница «Контакты» (данные приходят из маршрута)
|   GET|POST /signin         — регистрация: форма и обработка (AuthController)
|   GET|POST /login          — вход в аккаунт (доступен без auth)
|   POST /logout             — выход: токен, сессия, CSRF
*/

// Главную отдаёт метод home контроллера ArticleController (данные из БД + пагинация).
Route::get('/', [ArticleController::class, 'home'])->name('home');

/*
 | Одна новость: {article} — id записи в таблице articles.
 |
 | Лабораторная работа №14: посредник track.view сохраняет каждый просмотр
 | страницы в таблицу article_views — из неё команда stats:daily собирает
 | статистику для модераторов.
 */
Route::get('/news/{article}', [ArticleController::class, 'show'])
    ->middleware('track.view')
    ->name('news.show');

/*
|--------------------------------------------------------------------------
| CRUD новостей (лабораторная работа №5)
|--------------------------------------------------------------------------
| Route::resource создаёт сразу набор маршрутов:
|   GET    /articles           index
|   GET    /articles/create    create
|   POST   /articles           store
|   GET    /articles/{}/edit   edit
|   PUT    /articles/{}       update
|   DELETE /articles/{}       destroy
| Метод show не нужен — публичная страница новости живёт на /news/{article}.
*/
// CRUD доступен только авторизованным пользователям.
Route::middleware('auth:sanctum')->group(function () {
    Route::resource('articles', ArticleController::class)->except(['show']);

    // Комментарии: добавление и удаление (права — через CommentPolicy).
    // throttle:10,1,comments — защита от спама: не больше десяти комментариев в минуту.
    Route::post('/news/{article}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:10,1,comments')
        ->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    /*
     | Модерация комментариев (лабораторная работа №9).
     | Доступ проверяет шлюз manage-comments: только модератор.
     */
    Route::get('/moderation/comments', [CommentController::class, 'moderation'])
        ->name('moderation.comments');
    Route::post('/moderation/comments/{comment}/approve', [CommentController::class, 'approve'])
        ->name('comments.approve');
    Route::delete('/moderation/comments/{comment}/reject', [CommentController::class, 'reject'])
        ->name('comments.reject');

    /*
     | Уведомления читателей (лабораторная работа №12).
     | Переход по уведомлению из шапки сайта: отметить прочитанным
     | и открыть новость.
     */
    Route::get('/notifications/{notification}', [NotificationController::class, 'read'])
        ->name('notifications.read');
});

Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/contacts', function () {
    /*
     | Массив данных для страницы «Контакты».
     | Передаём его в шаблон вторым аргументом метода view():
     | в Blade он будет доступен как переменная $contacts.
     */
    $contacts = [
        [
            'title' => 'Приёмная комиссия',
            'phone' => '+7 (495) 223-05-23',
            'phone_link' => '+74952230523',
            'email' => 'priem@mospolytech.ru',
            'address' => 'ул. Большая Семёновская, д. 38',
        ],
        [
            'title' => 'Деканат факультета информационных технологий',
            'phone' => '+7 (495) 223-05-23, доб. 1122',
            'phone_link' => '+74952230523',
            'email' => 'fit@mospolytech.ru',
            'address' => 'ул. Большая Семёновская, д. 38, ауд. А-212',
        ],
        [
            'title' => 'Учебно-методическое управление',
            'phone' => '+7 (495) 223-05-23, доб. 1155',
            'phone_link' => '+74952230523',
            'email' => 'umu@mospolytech.ru',
            'address' => 'ул. Автозаводская, д. 16, ауд. 2204',
        ],
    ];

    return view('pages.contacts', [
        'contacts' => $contacts,
    ]);
})->name('contacts');

/*
|--------------------------------------------------------------------------
| Регистрация и авторизация (лабораторные работы №3 и №6)
|--------------------------------------------------------------------------
| Регистрация: GET /signin — форма, POST /signin — сохранение в БД.
| Вход:        GET /login  — форма, POST /login  — вход + токен Sanctum.
| Выход:       POST /logout — удаление токена, аннулирование сессии, новый CSRF.
| Формы входа и регистрации доступны без auth:sanctum — иначе на них
| не смог бы попасть неавторизованный пользователь.
*/

/*
 | throttle:6,1,<name> — не больше шести попыток в минуту с одного IP.
 | Третий параметр задаёт отдельный счётчик для входа и для регистрации,
 | чтобы пять неудачных входов не блокировали регистрацию. Без ограничения
 | пароль можно перебирать сколь угодно быстро; при превышении Laravel
 | вернёт 429 Too Many Requests.
 */
Route::get('/signin', [AuthController::class, 'create'])->name('signin');
Route::post('/signin', [AuthController::class, 'registration'])
    ->middleware('throttle:6,1,register')
    ->name('signin.registration');

Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1,login')
    ->name('login.attempt');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum')
    ->name('logout');
