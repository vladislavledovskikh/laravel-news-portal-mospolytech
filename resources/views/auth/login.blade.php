@extends('layouts.app')

@section('title', 'Вход')

@section('content')
    <div class="auth">
        <h1 class="page__title">Вход в аккаунт</h1>
        <p class="page__lead">
            Нет аккаунта?
            <a class="link" href="{{ route('signin') }}">Зарегистрируйтесь</a>.
        </p>

        <form class="form" method="POST" action="{{ route('login.attempt') }}">
            {{-- CSRF-токен: без него POST-запрос будет отклонён (419) --}}
            @csrf

            <label class="field">
                <span class="field__label">E-mail</span>
                <input
                    class="field__input @error('email') field__input--error @enderror"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="user@example.com"
                    autocomplete="email">
                @error('email')
                    <span class="field__error">{{ $message }}</span>
                @enderror
            </label>

            <label class="field">
                <span class="field__label">Пароль</span>
                <input
                    class="field__input @error('password') field__input--error @enderror"
                    type="password"
                    name="password"
                    placeholder="Ваш пароль"
                    autocomplete="current-password">
                @error('password')
                    <span class="field__error">{{ $message }}</span>
                @enderror
            </label>

            <button class="btn btn--primary" type="submit">Войти</button>
        </form>
    </div>
@endsection
