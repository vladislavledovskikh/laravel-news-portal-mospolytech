<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'user_id' => User::factory(),
            // realText() даёт русский текст, sentence() — латинский lorem.
            'body' => Str::limit(trim(preg_replace('/\s+/u', ' ', fake()->realText(400))), 150),
            // По умолчанию комментарий ждёт проверки модератором.
            'is_approved' => false,
            'approved_at' => null,
        ];
    }

    /** Комментарий уже прошёл модерацию. */
    public function approved(): static
    {
        return $this->state(fn () => [
            'is_approved' => true,
            'approved_at' => now(),
        ]);
    }
}
