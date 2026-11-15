@extends('layouts.app')

@section('title', 'Новая новость')

@section('content')
    <nav class="breadcrumbs">
        <a href="{{ route('articles.index') }}">← Управление новостями</a>
    </nav>

    <div class="auth">
        <h1 class="page__title">Новая новость</h1>

        <form class="form" method="POST" action="{{ route('articles.store') }}">
            @include('articles._form')
        </form>
    </div>
@endsection
