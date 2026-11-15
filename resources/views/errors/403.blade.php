@extends('layouts.app')

@section('title', 'Доступ запрещён')

@section('content')
    <div class="auth">
        <p class="hero__badge">403</p>
        <h1 class="page__title">Доступ запрещён</h1>
        @php
            $text = $message
                ?? (isset($exception) && $exception->getMessage() ? $exception->getMessage() : null)
                ?? 'Недостаточно прав для этого действия.';
        @endphp

        <p class="page__lead">{{ $text }}</p>

        <div class="hero__actions">
            <a class="btn btn--primary" href="{{ route('home') }}">На главную</a>
        </div>
    </div>
@endsection
