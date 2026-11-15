@extends('layouts.app')

@section('title', 'Модерация комментариев')

@section('content')
    <section class="moderation">
        <div class="moderation__head">
            <div>
                <h1 class="page__title">Модерация комментариев</h1>
                <p class="page__lead">
                    Здесь только новые комментарии, которые ещё не прошли проверку.
                    На странице новости видны лишь принятые.
                </p>
            </div>
            <span class="moderation__counter">
                Ждут проверки: {{ $comments->total() }}
            </span>
        </div>

        @if ($comments->isEmpty())
            <p class="empty">Новых комментариев на модерации нет.</p>
        @else
            @foreach ($comments as $comment)
                <article class="comment comment--pending">
                    <div class="comment__head">
                        <span class="comment__author">{{ $comment->user->name }}</span>
                        <span class="comment__date">{{ $comment->created_at->format('d.m.Y H:i') }}</span>
                        <span class="comment__pending">На модерации</span>

                        <div class="moderation__actions">
                            {{-- Принять: комментарий появится на странице новости --}}
                            <form class="moderation__form" method="POST"
                                  action="{{ route('comments.approve', $comment) }}">
                                @csrf
                                <button class="btn btn--primary btn--sm" type="submit">Принять</button>
                            </form>

                            {{-- Отклонить: комментарий удаляется --}}
                            <form class="moderation__form" method="POST"
                                  action="{{ route('comments.reject', $comment) }}"
                                  onsubmit="return confirm('Отклонить и удалить комментарий?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn--danger btn--sm" type="submit">Отклонить</button>
                            </form>
                        </div>
                    </div>

                    <p class="comment__text">{{ $comment->body }}</p>

                    <p class="moderation__article">
                        Новость:
                        <a class="link" href="{{ route('news.show', $comment->article) }}">
                            {{ $comment->article->title }}
                        </a>
                    </p>
                </article>
            @endforeach

            <div class="pagination-wrap">
                {{ $comments->links('pagination.custom') }}
            </div>
        @endif
    </section>
@endsection
