@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Навигация по страницам">
        @if ($paginator->onFirstPage())
            <span class="pagination__item pagination__item--disabled">← Назад</span>
        @else
            <a class="pagination__item" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Назад</a>
        @endif

        @foreach ($elements as $element)
            {{-- Разделитель "..." между группами страниц --}}
            @if (is_string($element))
                <span class="pagination__item pagination__item--dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination__item pagination__item--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagination__item" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="pagination__item" href="{{ $paginator->nextPageUrl() }}" rel="next">Вперёд →</a>
        @else
            <span class="pagination__item pagination__item--disabled">Вперёд →</span>
        @endif
    </nav>
@endif
