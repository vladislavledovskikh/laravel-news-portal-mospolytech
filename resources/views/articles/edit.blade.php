@extends('layouts.app')

@section('title', 'Редактирование новости')

@section('content')
    <nav class="breadcrumbs">
        <a href="{{ route('articles.index') }}">← Управление новостями</a>
    </nav>

    <div class="auth">
        <h1 class="page__title">Редактирование новости #{{ $article->id }}</h1>

        <form class="form" method="POST" action="{{ route('articles.update', $article) }}">
            @include('articles._form')
        </form>
    </div>
@endsection
