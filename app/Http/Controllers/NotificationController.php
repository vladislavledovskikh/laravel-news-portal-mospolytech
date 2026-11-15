<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Уведомления читателей (лабораторная работа №12).
 */
class NotificationController extends Controller
{
    /**
     * Переход по уведомлению: отметить прочитанным и открыть новость.
     *
     * Ищем уведомление только среди своих — чужое вернёт 404.
     */
    public function read(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $record->markAsRead();

        return redirect()->to($record->data['url'] ?? route('home'));
    }
}
