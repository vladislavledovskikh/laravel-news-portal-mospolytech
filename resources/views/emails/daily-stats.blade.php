<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика сайта за {{ $date->format('d.m.Y') }}</title>
</head>
{{-- Письмо со статистикой за день (лабораторная работа №14).
     Почтовые клиенты не понимают внешний CSS, поэтому стили инлайн. --}}
<body style="margin:0; padding:0; background:#f4f6fb; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; color:#1f2937;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb; padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:600px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,0.08);">

                {{-- Шапка письма --}}
                <tr>
                    <td style="padding:24px 32px; background:#111827; color:#ffffff;">
                        <p style="margin:0; font-size:13px; letter-spacing:1px; text-transform:uppercase; opacity:0.7;">
                            Новости МосПолитеха
                        </p>
                        <p style="margin:6px 0 0; font-size:20px; font-weight:700;">
                            Статистика сайта за {{ $date->locale('ru')->translatedFormat('j F Y') }}
                        </p>
                    </td>
                </tr>

                {{-- Три ключевых числа --}}
                <tr>
                    <td style="padding:28px 32px 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="33.33%" style="padding:16px; background:#f3f4f6; border-radius:12px; text-align:center;">
                                    <p style="margin:0; font-size:28px; font-weight:700; color:#111827;">{{ $views }}</p>
                                    <p style="margin:6px 0 0; font-size:12px; letter-spacing:0.4px; text-transform:uppercase; color:#6b7280;">
                                        просмотров новостей
                                    </p>
                                </td>
                                <td width="8"></td>
                                <td width="33.33%" style="padding:16px; background:#f3f4f6; border-radius:12px; text-align:center;">
                                    <p style="margin:0; font-size:28px; font-weight:700; color:#111827;">{{ $comments }}</p>
                                    <p style="margin:6px 0 0; font-size:12px; letter-spacing:0.4px; text-transform:uppercase; color:#6b7280;">
                                        новых комментариев
                                    </p>
                                </td>
                                <td width="8"></td>
                                <td width="33.33%" style="padding:16px; background:#f3f4f6; border-radius:12px; text-align:center;">
                                    <p style="margin:0; font-size:28px; font-weight:700; color:#111827;">{{ $commentsPending }}</p>
                                    <p style="margin:6px 0 0; font-size:12px; letter-spacing:0.4px; text-transform:uppercase; color:#6b7280;">
                                        ждут модерации
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Самые читаемые новости за день --}}
                <tr>
                    <td style="padding:28px 32px 8px;">
                        <p style="margin:0 0 14px; font-size:15px; font-weight:700; color:#111827;">
                            Самые читаемые новости
                        </p>

                        @if (empty($topArticles))
                            <p style="margin:0 0 20px; font-size:14px; color:#6b7280;">
                                Сегодня новости ещё не открывали.
                            </p>
                        @else
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                   style="font-size:14px; border-collapse:collapse;">
                                <tr>
                                    <td colspan="2" style="padding:0 0 6px; font-size:11px; letter-spacing:0.6px; text-transform:uppercase; color:#9ca3af;">
                                        Новость
                                    </td>
                                    <td style="padding:0 0 6px; width:82px; text-align:right; font-size:11px; letter-spacing:0.6px; text-transform:uppercase; color:#9ca3af;">
                                        Просмотры
                                    </td>
                                </tr>
                                @foreach ($topArticles as $index => $row)
                                    <tr>
                                        <td style="padding:10px 0; border-top:1px solid #e5e7eb; width:24px; color:#9ca3af; vertical-align:top;">
                                            {{ $index + 1 }}
                                        </td>
                                        <td style="padding:10px 0; border-top:1px solid #e5e7eb; vertical-align:top;">
                                            <a href="{{ url($row['url']) }}"
                                               style="color:#111827; text-decoration:none; font-weight:600;">
                                                {{ $row['title'] }}
                                            </a>
                                            <br>
                                            <span style="font-size:12px; color:#9ca3af;">{{ $row['url'] }}</span>
                                        </td>
                                        <td style="padding:10px 0; border-top:1px solid #e5e7eb; width:82px; text-align:right; vertical-align:top; white-space:nowrap; color:#374151;">
                                            {{ $row['views'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </td>
                </tr>

                {{-- Подвал --}}
                <tr>
                    <td style="padding:18px 32px 26px; border-top:1px solid #e5e7eb;">
                        <p style="margin:0; font-size:12px; line-height:1.5; color:#9ca3af;">
                            Письмо отправлено автоматически планировщиком задач по команде <code>stats:daily</code>.
                            Данные собираются из таблиц просмотров новостей и комментариев.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
