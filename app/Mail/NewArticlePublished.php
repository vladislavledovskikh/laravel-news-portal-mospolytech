<?php

namespace App\Mail;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Письмо модератору о новой новости (лабораторная работа №8).
 *
 * В конструктор передаётся сама статья: модель сериализуется в письме
 * (SerializesModels), а в шаблоне доступны заголовок, дата и картинка превью.
 */
class NewArticlePublished extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Article  $article  опубликованная новость
     * @param  string|null  $author  имя того, кто её добавил
     */
    public function __construct(
        public Article $article,
        public ?string $author = null,
    ) {}

    /**
     * Собирает письмо: отправитель, тема, шаблон и данные для него.
     *
     * Адрес отправителя берётся из config/mail.php (MAIL_FROM_ADDRESS
     * и MAIL_FROM_NAME из .env).
     */
    public function build(): static
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Новая новость на сайте: '.$this->article->title)
            ->view('emails.new-article')
            ->with([
                'article' => $this->article,
                'url' => route('news.show', $this->article),
                'preview' => asset('images/'.$this->article->preview_image),
                'author' => $this->author,
            ]);
    }
}
