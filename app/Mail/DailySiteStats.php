<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Письмо со статистикой сайта за день (лабораторная работа №14).
 *
 * Отправляется командой stats:daily по расписанию всем модераторам.
 */
class DailySiteStats extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  CarbonInterface  $date  день, за который собрана статистика
     * @param  int  $views  сколько раз открывали новости
     * @param  int  $comments  сколько комментариев добавили
     * @param  int  $commentsPending  сколько из них ждёт модерации
     * @param  array<int, array{url: string, title: string, views: int}>  $topArticles  самые читаемые новости
     */
    public function __construct(
        public CarbonInterface $date,
        public int $views,
        public int $comments,
        public int $commentsPending,
        public array $topArticles = [],
    ) {}

    /**
     * Адрес отправителя берётся из config/mail.php (MAIL_FROM_ADDRESS / MAIL_FROM_NAME).
     */
    public function build(): static
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Статистика сайта за '.$this->date->format('d.m.Y'))
            ->view('emails.daily-stats');
    }
}
