@extends('layouts.app')

@section('title', 'Новости')

@section('content')
    <section class="hero hero--compact">
        <p class="hero__badge">Новости</p>
        <h1 class="hero__title">Привет! Это мой первый сайт на Laravel</h1>
        <p class="hero__text">
            Свежие новости, события и объявления. Нажмите на превью,
            чтобы открыть новость целиком и оставить комментарий.
        </p>
    </section>

    @if ($articles->isEmpty())
        <p class="empty">
            Пока нет ни одной новости. Выполните <code>php artisan migrate --seed</code>.
        </p>
    @else
        <div class="news">
            @foreach ($articles as $article)
                <article class="news-card">
                    {{-- Превью — ссылка на страницу новости --}}
                    <a class="news-card__media" href="{{ route('news.show', $article) }}">
                        <img src="{{ asset('images/' . $article->preview_image) }}"
                             alt="Превью: {{ $article->title }}"
                             loading="lazy">
                    </a>

                    <div class="news-card__body">
                        <p class="news-card__date">{{ $article->published_at->format('d.m.Y') }}</p>

                        <h2 class="news-card__title">
                            <a href="{{ route('news.show', $article) }}">{{ $article->title }}</a>
                        </h2>

                        @if ($article->short_description)
                            <p class="news-card__text">{{ $article->short_description }}</p>
                        @endif

                        <a class="news-card__more" href="{{ route('news.show', $article) }}">
                            Читать далее →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Пагинация: по 6 новостей на страницу --}}
        <div class="pagination-wrap">
            {{ $articles->links('pagination.custom') }}
        </div>
    @endif
@endsection
