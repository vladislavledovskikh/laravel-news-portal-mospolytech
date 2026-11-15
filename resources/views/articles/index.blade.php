@extends('layouts.app')

@section('title', 'Управление новостями')

@section('content')
    <div class="admin-head">
        <div>
            <h1 class="page__title">Управление новостями</h1>
            <p class="page__lead">Всего новостей: {{ $articles->total() }}</p>
        </div>

        @can('create', App\Models\Article::class)
            <a class="btn btn--primary" href="{{ route('articles.create') }}">+ Создать новость</a>
        @endcan
    </div>

    @if ($articles->isEmpty())
        <p class="empty">Новостей пока нет. Создайте первую.</p>
    @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="table__num">#</th>
                        <th>Заголовок</th>
                        <th class="table__num">Дата</th>
                        <th class="table__num">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($articles as $article)
                        <tr>
                            <td class="table__num">{{ $article->id }}</td>

                            <td>
                                <a class="table__title" href="{{ route('news.show', $article) }}">
                                    {{ $article->title }}
                                </a>
                                @if ($article->short_description)
                                    <span class="table__hint">{{ $article->short_description }}</span>
                                @endif
                            </td>

                            <td class="table__num">{{ $article->published_at->format('d.m.Y') }}</td>

                            <td class="table__num">
                                <div class="table__actions">
                                    @can('update', $article)
                                        <a class="table__link" href="{{ route('articles.edit', $article) }}">Изменить</a>
                                    @endcan

                                    {{-- Удаление только POST-запросом с CSRF-токеном и методом DELETE --}}
                                    @can('delete', $article)
                                        <form class="table__form" method="POST"
                                              action="{{ route('articles.destroy', $article) }}"
                                              onsubmit="return confirm('Удалить эту новость?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="table__link table__link--danger" type="submit">
                                                Удалить
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $articles->links('pagination.custom') }}
        </div>
    @endif
@endsection
