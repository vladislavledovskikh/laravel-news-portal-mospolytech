@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <div class="auth">
        <h1 class="page__title">Регистрация</h1>
        <p class="page__lead">
            Создайте аккаунт: после регистрации вы автоматически перейдёте
            на форму входа.
        </p>

        <form class="form" method="POST" action="{{ route('signin.registration') }}">
            {{-- CSRF-токен: без него Laravel отклонит POST-запрос (419) --}}
            @csrf

            <label class="field">
                <span class="field__label">Имя</span>
                <input
                    class="field__input @error('name') field__input--error @enderror"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Владислав"
                    autocomplete="name">
                @error('name')
                    <span class="field__error">{{ $message }}</span>
                @enderror
            </label>

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
                    placeholder="Минимум 6 символов"
                    autocomplete="new-password">
                @error('password')
                    <span class="field__error">{{ $message }}</span>
                @enderror
            </label>

            <button class="btn btn--primary" type="submit">Зарегистрироваться</button>

            <p class="form__hint">
                Уже есть аккаунт?
                <a class="link" href="{{ route('login') }}">Войти</a>.
            </p>
        </form>
    </div>
@endsection
