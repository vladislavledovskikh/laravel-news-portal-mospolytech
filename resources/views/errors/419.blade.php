@extends('layouts.app')

@section('title', 'Сессия истекла')

{{-- 419 приходит, когда истёк CSRF-токен: страница была открыта слишком давно.
     Вместо стандартной английской ошибки объясняем, что делать дальше. --}}
@section('content')
    <div class="auth">
        <p class="hero__badge">419</p>
        <h1 class="page__title">Сессия истекла</h1>
        <p class="page__lead">
            Страница была открыта слишком давно. Обновите её и повторите действие.
        </p>

        <div class="hero__actions">
            <a class="btn btn--primary" href="{{ route('home') }}">На главную</a>
            <a class="btn btn--ghost" href="{{ url()->previous() }}">Вернуться назад</a>
        </div>
    </div>
@endsection
