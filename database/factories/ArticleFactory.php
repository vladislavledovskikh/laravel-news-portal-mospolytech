<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use OverflowException;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /** Счётчик, чтобы картинки в сидере чередовались, а не выбирались случайно. */
    private static int $imageIndex = 0;

    /**
     * Заголовки демонстрационных новостей.
     *
     * Faker при локали ru_RU умеет только «книжный» текст (realText),
     * поэтому осмысленные заголовки заданы списком — иначе в карточках
     * появлялись бы обрывки фраз вида «…как два».
     */
    private const HEADLINES = [
        'Студенты представили проект на конференции',
        'Итоги недели: что нового на кафедре',
        'Открыт набор в проектную мастерскую',
        'Как прошёл хакатон по веб-разработке',
        'Обновлены правила сдачи лабораторных работ',
        'Встреча с выпускниками: опыт работы в индустрии',
        'Изменён график консультаций преподавателей',
        'Команда университета победила в олимпиаде',
        'Запущен курс по серверной разработке',
        'Студенческий совет приглашает на встречу',
        'Практика в IT-компаниях: как подать заявку',
        'Разбор ошибок: чему научил первый проект',
        'Новые лаборатории открылись в корпусе на Автозаводской',
        'Студенты прошли стажировку в крупной IT-компании',
        'Объявлен конкурс студенческих проектов',
        'Лекция о безопасности веб-приложений пройдёт в пятницу',
        'Библиотека продлила доступ к электронным ресурсам',
        'Как подготовиться к защите курсовой работы',
        'Волонтёрская акция собрала более ста участников',
        'Итоги семестра: цифры и планы на будущее',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Пары картинок проекта: превью и полное изображение одного сюжета.
        $images = [
            ['preview.jpg', 'full.jpeg'],
            ['preview_2.jpg', 'full_2.jpeg'],
            ['preview_3.jpg', 'full_3.jpeg'],
            ['preview_4.jpg', 'full_4.jpeg'],
        ];

        [$previewImage, $fullImage] = $images[self::$imageIndex++ % count($images)];

        return [
            'title' => $this->headline(),
            'short_description' => Str::limit($this->narrative(400), 140),
            'content' => implode("\n\n", [
                Str::limit($this->narrative(800), 420),
                Str::limit($this->narrative(800), 420),
                Str::limit($this->narrative(600), 260),
            ]),
            // Последние четыре месяца: главная страница выглядит «живой»,
            // а статьи из articles.json остаются более ранним архивом.
            'published_at' => $this->faker->dateTimeBetween('-4 months', 'now')->format('Y-m-d'),
            'preview_image' => $previewImage,
            'full_image' => $fullImage,
        ];
    }

    /**
     * realText() берёт осмысленный русский текст (при APP_FAKER_LOCALE=ru_RU),
     * в отличие от sentence()/paragraphs(), которые всегда дают латинский lorem.
     */
    private function plainText(int $chars): string
    {
        return trim(preg_replace('/\s+/u', ' ', $this->faker->realText($chars)));
    }

    /**
     * Повествовательный текст без реплик диалога: в карточке новости
     * фразы вида «— Нет, матушка, не обижу, — говорил он» выглядят странно.
     */
    private function narrative(int $chars): string
    {
        $text = $this->plainText($chars);
        $sentences = preg_split('/(?<=[.!?])\s+/u', $text) ?: [];

        $clean = array_filter(
            $sentences,
            fn (string $sentence) => ! preg_match('/[—«»"]/u', $sentence)
        );

        return $clean ? implode(' ', $clean) : $text;
    }

    /** Заголовок из списка: стараемся не повторяться. */
    private function headline(): string
    {
        try {
            return $this->faker->unique()->randomElement(self::HEADLINES);
        } catch (OverflowException) {
            // Заголовки в списке закончились — дальше допускаем повторы.
            return $this->faker->randomElement(self::HEADLINES);
        }
    }
}
