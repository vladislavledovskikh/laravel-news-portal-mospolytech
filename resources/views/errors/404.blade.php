@extends('layouts.app')

@section('title', 'Страница не найдена')

{{-- Своя страница 404: без неё Laravel показывает стандартную английскую
     «Not Found», которая выбивается из оформления сайта. --}}
@section('content')
    <div class="auth">
        <p class="hero__badge">404</p>
        <h1 class="page__title">Страница не найдена</h1>
        <p class="page__lead">
            Возможно, адрес изменился или новость удалили.
        </p>

        <div class="hero__actions">
            <a class="btn btn--primary" href="{{ route('home') }}">На главную</a>
        </div>
    </div>
@endsection
