<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /* ------------------------------------------------------------------
     | Регистрация
     * ------------------------------------------------------------------ */

    /**
     * Форма регистрации (GET /signin).
     */
    public function create()
    {
        return view('auth.signin');
    }

    /**
     * Сохранение нового пользователя (POST /signin).
     *
     * Сначала валидация, потом запись в БД, потом редирект на форму входа.
     * Если запрос ждёт JSON (Accept: application/json) — возвращаем JSON.
     */
    public function registration(Request $request)
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

        // Новый пользователь получает роль «читатель» (лабораторная работа №7).
        $data['role_id'] = Role::where('code', Role::READER)->value('id');

        // Пароль хешируется автоматически: в модели User каст 'password' => 'hashed'.
        $user = User::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Пользователь успешно зарегистрирован',
                // Пароль (даже хеш) в ответ не отдаём.
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 201);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Регистрация завершена. Войдите под своим e-mail.');
    }

    /* ------------------------------------------------------------------
     | Авторизация
     * ------------------------------------------------------------------ */

    /**
     * Форма входа (GET /login).
     */
    public function loginForm()
    {
        return view('auth.login');
    }

    /**
     * Аутентификация (POST /login).
     *
     * При успехе: вход в сессию, новый токен Sanctum, редирект на главную.
     * Маршрут доступен без middleware auth — иначе на форму входа было бы не попасть.
     */
    public function login(Request $request)
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

        if (! Auth::attempt($credentials)) {
            // Возвращаем на форму с одним общим сообщением, не раскрывая,
            // что именно неверно — e-mail или пароль.
            throw ValidationException::withMessages([
                'email' => 'Неверный e-mail или пароль.',
            ]);
        }

        // Новый идентификатор сессии — защита от фиксации сессии.
        $request->session()->regenerate();

        // Токен аутентификации пользователя (Sanctum).
        // В БД хранится только хеш, поэтому запоминаем id токена, чтобы
        // удалить именно его при выходе.
        $token = $request->user()->createToken('web');
        $request->session()->put('auth_token_id', $token->accessToken->id);

        return redirect()
            ->intended(route('home'))
            ->with('status', 'Вы вошли как '.$request->user()->name);
    }

    /**
     * Выход (POST /logout): удаляем токен, аннулируем сессию и обновляем CSRF.
     */
    public function logout(Request $request)
    {
        // 1. Удаляем токен аутентификации текущей сессии.
        $tokenId = $request->session()->get('auth_token_id');

        if ($tokenId) {
            $request->user()->tokens()->whereKey($tokenId)->delete();
        }

        // 2. Выходим из сессии.
        Auth::guard('web')->logout();

        // 3. Аннулируем сессию и обновляем CSRF-токен.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('status', 'Вы вышли из аккаунта.');
    }
}
