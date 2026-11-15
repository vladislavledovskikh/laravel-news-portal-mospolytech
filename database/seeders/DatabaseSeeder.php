<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Роли: модератор и читатель.
        $this->call(RoleSeeder::class);

        $moderatorRole = Role::where('code', Role::MODERATOR)->firstOrFail();
        $readerRole = Role::where('code', Role::READER)->firstOrFail();

        // 2. Модератор — ему доступны любые действия с новостями и комментариями.
        User::updateOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Модератор',
                'password' => 'password',
                'role_id' => $moderatorRole->id,
            ]
        );

        // 3. Читатель — только просмотр и комментарии.
        User::updateOrCreate(
            ['email' => 'reader@example.com'],
            [
                'name' => 'Читатель',
                'password' => 'password',
                'role_id' => $readerRole->id,
            ]
        );

        // Пользователь из предыдущих лабораторных — тоже читатель.
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Тестовый пользователь',
                'password' => 'password',
                'role_id' => $readerRole->id,
            ]
        );

        /*
         | 4. Новости: из json-файла и сгенерированные фабрикой.
         | Наполняем только пустую таблицу, чтобы повторный `php artisan db:seed`
         | не создавал дубликаты демо-данных.
         */
        if (Article::query()->exists()) {
            return;
        }

        $this->importFromJson();
        Article::factory()->count(10)->create();

        $this->seedComments();
    }

    /**
     * Демонстрационные комментарии (лабораторная работа №9):
     * часть уже прошла модерацию, часть ждёт проверки.
     */
    private function seedComments(): void
    {
        $readers = User::query()
            ->whereHas('role', fn ($query) => $query->where('code', Role::READER))
            ->get();

        $articles = Article::query()->orderBy('id')->take(2)->get();

        if ($readers->isEmpty() || $articles->isEmpty()) {
            return;
        }

        $approved = [
            'Отличная новость, спасибо за подробности!',
            'Полезно, давно ждал такой информации.',
        ];

        $pending = [
            'А будет продолжение? Хочется больше деталей.',
            'Спасибо! Пришёл сюда из рассылки.',
        ];

        foreach ($articles as $index => $article) {
            Comment::factory()->approved()->create([
                'article_id' => $article->id,
                'user_id' => $readers[$index % $readers->count()]->id,
                'body' => $approved[$index % count($approved)],
            ]);

            Comment::factory()->create([
                'article_id' => $article->id,
                'user_id' => $readers[($index + 1) % $readers->count()]->id,
                'body' => $pending[$index % count($pending)],
            ]);
        }
    }

    /**
     * Читает public/articles.json и сохраняет статьи в таблицу articles.
     */
    private function importFromJson(): void
    {
        $path = public_path('articles.json');

        if (! is_file($path)) {
            return;
        }

        $articles = json_decode(file_get_contents($path), true) ?: [];

        foreach ($articles as $article) {
            // firstOrCreate — чтобы повторный запуск сидера не дублировал новости.
            Article::firstOrCreate(
                ['title' => $article['name']],
                [
                    'short_description' => $article['shortDesc'] ?? null,
                    'content' => $article['desc'],
                    'published_at' => date('Y-m-d', strtotime($article['date'])),
                    'preview_image' => $article['preview_image'],
                    'full_image' => $article['full_image'],
                ]
            );
        }
    }
}
