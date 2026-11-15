{{-- Общая часть формы: используется и при создании, и при редактировании --}}

@if ($errors->any())
    <div class="alert alert--error">
        <p class="alert__title">Проверьте поля формы:</p>
        <ul class="alert__list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@csrf
{{-- При редактировании форма отправляется методом PUT --}}
@if ($article->exists)
    @method('PUT')
@endif

<label class="field">
    <span class="field__label">Заголовок</span>
    <input class="field__input @error('title') field__input--error @enderror"
           type="text" name="title" value="{{ old('title', $article->title) }}"
           placeholder="О чём новость">
    @error('title')
        <span class="field__error">{{ $message }}</span>
    @enderror
</label>

<label class="field">
    <span class="field__label">Краткое описание</span>
    <textarea class="field__input field__input--area @error('short_description') field__input--error @enderror"
              name="short_description" rows="2"
              placeholder="Одна строка для карточки на главной">{{ old('short_description', $article->short_description) }}</textarea>
    @error('short_description')
        <span class="field__error">{{ $message }}</span>
    @enderror
</label>

<label class="field">
    <span class="field__label">Текст новости</span>
    <textarea class="field__input field__input--area @error('content') field__input--error @enderror"
              name="content" rows="8"
              placeholder="Полный текст новости">{{ old('content', $article->content) }}</textarea>
    @error('content')
        <span class="field__error">{{ $message }}</span>
    @enderror
</label>

<label class="field">
    <span class="field__label">Дата публикации</span>
    <input class="field__input @error('published_at') field__input--error @enderror"
           type="date" name="published_at"
           value="{{ old('published_at', $article->published_at?->format('Y-m-d')) }}">
    @error('published_at')
        <span class="field__error">{{ $message }}</span>
    @enderror
</label>

<div class="form__row">
    <label class="field">
        <span class="field__label">Превью (картинка в списке)</span>
        <select class="field__input @error('preview_image') field__input--error @enderror" name="preview_image">
            @foreach ($images as $image)
                <option value="{{ $image }}" @selected(old('preview_image', $article->preview_image) === $image)>
                    {{ $image }}
                </option>
            @endforeach
        </select>
        @error('preview_image')
            <span class="field__error">{{ $message }}</span>
        @enderror
    </label>

    <label class="field">
        <span class="field__label">Полное изображение</span>
        <select class="field__input @error('full_image') field__input--error @enderror" name="full_image">
            @foreach ($images as $image)
                <option value="{{ $image }}" @selected(old('full_image', $article->full_image) === $image)>
                    {{ $image }}
                </option>
            @endforeach
        </select>
        @error('full_image')
            <span class="field__error">{{ $message }}</span>
        @enderror
    </label>
</div>

<div class="form__actions">
    <button class="btn btn--primary" type="submit">
        {{ $article->exists ? 'Сохранить изменения' : 'Создать новость' }}
    </button>
    <a class="btn btn--ghost" href="{{ route('articles.index') }}">Отмена</a>
</div>
