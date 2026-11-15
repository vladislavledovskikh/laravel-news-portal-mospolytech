<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Таблица просмотров новостей (лабораторная работа №14).
     *
     * Каждая строка — один запрос страницы новости: её адрес (url)
     * и статья, которую открыли. По этим записям команда stats:daily
     * считает, сколько просмотров было за день.
     */
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();

            // У записи о просмотре есть только дата создания, updated_at не нужен.
            $table->timestamp('created_at')->useCurrent();

            // Статистика выбирает записи «за сегодня» и группирует их по url.
            $table->index('created_at');
            $table->index(['article_id', 'created_at']);
        });
    }

    /**
     * Откат миграции.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_views');
    }
};
