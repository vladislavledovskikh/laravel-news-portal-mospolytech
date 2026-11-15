<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Описание для поисковиков и превью ссылки в мессенджерах.
         На отдельных страницах его можно переопределить через @section('description'). --}}
    <meta name="description" content="@yield('description', 'Новости Московского Политехнического университета: события кафедры, студенческие проекты и объявления.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <title>@yield('title', 'Главная') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    {{-- Собранные Vite ресурсы: стили Vue-компонента и JS с Laravel Echo --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

    {{-- ===================== Header: меню ===================== --}}
    <header class="site-header">
        <div class="container header__inner">
            <a href="{{ route('home') }}" class="logo">
                <span class="logo__mark">МП</span>
                <span class="logo__text">{{ config('app.name') }}</span>
            </a>

            <nav class="nav">
                {{-- request()->routeIs() подсвечивает активный пункт меню --}}
                <a href="{{ route('home') }}"
                   class="nav__link {{ request()->routeIs('home') ? 'nav__link--active' : '' }}">
                    Главная
                </a>
                <a href="{{ route('about') }}"
                   class="nav__link {{ request()->routeIs('about') ? 'nav__link--active' : '' }}">
                    О нас
                </a>
                <a href="{{ route('contacts') }}"
                   class="nav__link {{ request()->routeIs('contacts') ? 'nav__link--active' : '' }}">
                    Контакты
                </a>
                @auth
                    {{-- Вкладка «Создать новость» — только для модератора (ArticlePolicy@create) --}}
                    @can('create', App\Models\Article::class)
                        <a href="{{ route('articles.create') }}"
                           class="nav__link {{ request()->routeIs('articles.create') ? 'nav__link--active' : '' }}">
                            Создать новость
                        </a>
                    @endcan

                    {{-- Управление новостями — тоже только модератору (ArticlePolicy@viewAny) --}}
                    @can('viewAny', App\Models\Article::class)
                        <a href="{{ route('articles.index') }}"
                           class="nav__link {{ request()->routeIs('articles.*') ? 'nav__link--active' : '' }}">
                            Управление
                        </a>
                    @endcan

                    {{-- Модерация комментариев — только модератору (шлюз manage-comments) --}}
                    @can('manage-comments')
                        <a href="{{ route('moderation.comments') }}"
                           class="nav__link {{ request()->routeIs('moderation.*') ? 'nav__link--active' : '' }}">
                            Модерация
                        </a>
                    @endcan

                    {{--
                        Уведомления читателя (лабораторная работа №12).
                        Список непрочитанных и их количество; клик по заголовку
                        отмечает уведомление прочитанным и открывает новость.
                        <details> раскрывается без JavaScript.
                    --}}
                    <details class="notif">
                        <summary class="notif__summary">
                            Уведомления
                            @if ($unreadNotifications->isNotEmpty())
                                <span class="notif__badge">{{ $unreadNotifications->count() }}</span>
                            @endif
                        </summary>

                        <div class="notif__panel">
                            @forelse ($unreadNotifications as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}"
                                   class="notif__item">
                                    <span class="notif__item-title">{{ $notification->data['title'] }}</span>
                                    <span class="notif__item-date">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </a>
                            @empty
                                <p class="notif__empty">Новых уведомлений нет</p>
                            @endforelse
                        </div>
                    </details>

                    <span class="nav__user" title="Вы вошли как {{ auth()->user()->email }}">
                        {{ auth()->user()->name }}
                    </span>

                    <form class="nav__form" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="nav__link nav__link--button" type="submit">Выйти</button>
                    </form>
                @else
                    <a href="{{ route('signin') }}"
                       class="nav__link {{ request()->routeIs('signin*') ? 'nav__link--active' : '' }}">
                        Регистрация
                    </a>
                    <a href="{{ route('login') }}"
                       class="nav__link {{ request()->routeIs('login*') ? 'nav__link--active' : '' }}">
                        Вход
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- ===================== Основная часть ===================== --}}
    <main class="site-main">
        <div class="container">
            {{-- Сообщение после создания / изменения / удаления новости, входа и т.п. --}}
            @if (session('status'))
                <div class="alert alert--success">{{ session('status') }}</div>
            @endif

            {{-- Отказ в доступе: сообщение из политики или шлюза --}}
            @if (session('error'))
                <div class="alert alert--error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- ===================== Footer: ФИО, группа ===================== --}}
    <footer class="site-footer">
        <div class="container">
            <p class="site-footer__author">Ледовских Владислав Владимирович, группа 257-321</p>
            <p class="site-footer__note">Серверная веб-разработка — учебный проект на Laravel</p>
        </div>
    </footer>

    {{-- Сюда Vue монтирует компонент с онлайн-оповещением (лабораторная работа №11) --}}
    <div id="app"></div>

</body>
</html>
