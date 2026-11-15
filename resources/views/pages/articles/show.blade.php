@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <nav class="breadcrumbs">
        <a href="{{ route('home') }}">← Ко всем новостям</a>
    </nav>

    <article class="article">
        <p class="article__date">{{ $article->published_at->format('d.m.Y') }}</p>
        <h1 class="article__title">{{ $article->title }}</h1>

        <figure class="article__figure">
            {{-- Полное изображение новости --}}
            <img class="article__image"
                 src="{{ asset('images/' . $article->full_image) }}"
                 alt="{{ $article->title }}">
        </figure>

        <div class="article__text">{!! nl2br(e($article->content)) !!}</div>
    </article>

    <section class="comments">
        <h2 class="comments__title">
            Комментарии
            <span class="comments__count">{{ $article->comments->count() }}</span>
        </h2>

        {{-- Шлюз manage-comments (AuthServiceProvider): виден только модератору --}}
        @can('manage-comments')
            <p class="comments__hint">
                Вы вошли как модератор — можете удалять любые комментарии.
                @if ($pendingComments > 0)
                    На модерации: {{ $pendingComments }} —
                    <a class="link" href="{{ route('moderation.comments') }}">перейти к проверке</a>.
                @else
                    Новых комментариев на проверку нет.
                @endif
            </p>
        @endcan

        @forelse ($article->comments as $comment)
            <article class="comment">
                <div class="comment__head">
                    <span class="comment__author">{{ $comment->user->name }}</span>
                    <span class="comment__date">{{ $comment->created_at->format('d.m.Y H:i') }}</span>

                    {{-- Кнопка удаления — только модератору (CommentPolicy@delete) --}}
                    @can('delete', $comment)
                        <form class="comment__form" method="POST"
                              action="{{ route('comments.destroy', $comment) }}"
                              onsubmit="return confirm('Удалить комментарий?');">
                            @csrf
                            @method('DELETE')
                            <button class="table__link table__link--danger" type="submit">Удалить</button>
                        </form>
                    @endcan
                </div>

                <p class="comment__text">{{ $comment->body }}</p>
            </article>
        @empty
            <p class="empty">Комментариев пока нет.</p>
        @endforelse

        @auth
            <form class="form form--comment" method="POST" action="{{ route('comments.store', $article) }}">
                @csrf

                <label class="field">
                    <span class="field__label">Ваш комментарий</span>
                    <textarea class="field__input field__input--area @error('body') field__input--error @enderror"
                              name="body" rows="3"
                              placeholder="Напишите комментарий">{{ old('body') }}</textarea>
                    @error('body')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </label>

                <div class="form__actions">
                    <button class="btn btn--primary" type="submit">Отправить</button>
                </div>
            </form>
        @else
            <p class="comments__guest">
                Чтобы оставить комментарий,
                <a class="link" href="{{ route('login') }}">войдите</a>.
            </p>
        @endauth
    </section>
@endsection
