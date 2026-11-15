@extends('layouts.app')

@section('title', 'Контакты')

@section('content')
    <article class="page">
        <h1 class="page__title">Контакты</h1>
        <p class="page__lead">
            Ниже перечислены подразделения университета.
        </p>

        {{-- Динамический вывод: количество карточек зависит от размера массива --}}
        @if (count($contacts) === 0)
            <p class="empty">Список контактов пока пуст.</p>
        @else
            <div class="contacts">
                @foreach ($contacts as $contact)
                    <article class="contact">
                        <h2 class="contact__title">{{ $contact['title'] }}</h2>

                        <dl class="contact__list">
                            <dt>Телефон</dt>
                            <dd>
                                {{-- phone — отображаемый номер, phone_link — номер для tel:-ссылки --}}
                                <a href="tel:{{ $contact['phone_link'] }}">
                                    {{ $contact['phone'] }}
                                </a>
                            </dd>

                            <dt>E-mail</dt>
                            <dd>
                                <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a>
                            </dd>

                            <dt>Адрес</dt>
                            <dd>{{ $contact['address'] }}</dd>
                        </dl>
                    </article>
                @endforeach
            </div>
        @endif

        <p class="page__meta">
            Всего подразделений: <strong>{{ count($contacts) }}</strong>
        </p>
    </article>
@endsection
