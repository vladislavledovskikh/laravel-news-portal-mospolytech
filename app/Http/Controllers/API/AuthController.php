<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Аутентификация через REST API (лабораторная работа №15).
 *
 * Это копия веб-контроллера App\Http\Controllers\AuthController: логика
 * та же, но вместо `return view(...)` и `return redirect(...)` методы
 * возвращают `response()->json(...)`. Форм в API нет, поэтому вместо
 * отдачи HTML-страницы метод create() описывает поля регистрации в JSON.
 *
 * Токен выдаёт Sanctum: клиент присылает его в заголовке
 * Authorization: Bearer <token> для защищённых запросов.
 */
class AuthController extends Controller
{
    /**
     * Описание формы регистрации (GET /api/register).
     *
     * В веб-версии этот метод отдавал HTML-форму (view('auth.signin')),
     * в API — возвращает JSON-описание: какие поля и с какими правилами
     * ждёт POST /api/register. Удобно подсмотреть в Postman.
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'method' => 'POST',
            'url' => url('/api/register'),
            'fields' => [
                'name' => 'строка, 2–255 символов',
                'email' => 'e-mail, ещё не зарегистрированный на сайте',
                'password' => 'строка, не короче 6 символов',
            ],
        ]);
    }

    /**
     * Регистрация (POST /api/register).
     *
     * Новый пользователь получает роль «читатель». В ответ — данные
     * пользователя и токен Sanctum, чтобы сразу работать с API.
     */
    public function registration(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'name' => ['required', 'string', 'min:2', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6', 'max:255'],
            ],
            [
                'name.required' => 'Укажите имя.',
                'name.min' => 'Имя должно быть не короче 2 символов.',
                'email.required' => 'Укажите e-mail.',
                'email.email' => 'Введите корректный e-mail, например user@example.com.',
                'email.unique' => 'Пользователь с таким e-mail уже зарегистрирован.',
                'password.required' => 'Укажите пароль.',
                'password.min' => 'Пароль должен быть не короче 6 символов.',
            ]
        );

        $data['role_id'] = Role::where('code', Role::READER)->value('id');

        // Пароль хешируется автоматически: в модели User каст 'password' => 'hashed'.
        $user = User::create($data);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'status' => 'ok',
            'message' => 'Пользователь успешно зарегистрирован',
            // Пароль (даже хеш) в ответ не отдаём.
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => Role::READER,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Вход (POST /api/login).
     *
     * Вместо сессии и редиректа на главную — новый токен Sanctum в JSON.
     * На неверные данные отвечаем 422 с тем же сообщением, что и на сайте.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(
            [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ],
            [
                'email.required' => 'Укажите e-mail.',
                'email.email' => 'Введите корректный e-mail.',
                'password.required' => 'Укажите пароль.',
            ]
        );

        $user = User::where('email', $credentials['email'])->first();

        // Одно общее сообщение: не раскрываем, что именно неверно.
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Неверный e-mail или пароль.',
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'status' => 'ok',
            'message' => 'Вы вошли как '.$user->name,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role?->code,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Выход (POST /api/logout, нужен токен).
     *
     * Вместо аннулирования сессии — удаляем токен, которым пришёл запрос:
     * после этого он не пройдёт middleware auth:sanctum.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Вы вышли из аккаунта.',
        ]);
    }
}
