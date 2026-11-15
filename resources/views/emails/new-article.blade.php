<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая новость на сайте</title>
</head>
{{-- Письмо рендерится отдельно от макета сайта: почтовые клиенты
     не понимают внешний CSS, поэтому все стили — инлайн. --}}
<body style="margin:0; padding:0; background:#f4f6fb; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif; color:#1f2937;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb; padding:32px 16px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                   style="max-width:600px; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,0.08);">

                {{-- Шапка письма --}}
                <tr>
                    <td style="padding:24px 32px; background:#1e4fd8; color:#ffffff;">
                        <p style="margin:0; font-size:13px; letter-spacing:1px; text-transform:uppercase; opacity:0.85;">
                            Новости сайта
                        </p>
                        <p style="margin:6px 0 0; font-size:20px; font-weight:700;">
                            Опубликована новая новость
                        </p>
                    </td>
                </tr>

                {{-- Превью новости --}}
                @if ($article->preview_image)
                    <tr>
                        <td style="padding:0;">
                            <img src="{{ $preview }}" alt="{{ $article->title }}"
                                 style="display:block; width:100%; max-height:260px; object-fit:cover;">
                        </td>
                    </tr>
                @endif

                {{-- Содержимое письма --}}
                <tr>
                    <td style="padding:28px 32px 8px;">
                        <h1 style="margin:0 0 12px; font-size:22px; line-height:1.3; color:#111827;">
                            {{ $article->title }}
                        </h1>

                        <p style="margin:0 0 16px; font-size:13px; color:#6b7280;">
                            {{ $article->published_at->format('d.m.Y') }}
                            @if ($author)
                                · добавил {{ $author }}
                            @endif
                        </p>

                        @if ($article->short_description)
                            <p style="margin:0 0 16px; font-size:16px; line-height:1.55; color:#374151; font-weight:600;">
                                {{ $article->short_description }}
                            </p>
                        @endif

                        <p style="margin:0 0 24px; font-size:15px; line-height:1.65; color:#4b5563;">
                            {!! nl2br(e(\Illuminate\Support\Str::limit($article->content, 500))) !!}
                        </p>

                        {{-- Кнопка-ссылка на страницу новости --}}
                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
                            <tr>
                                <td style="border-radius:10px; background:#1e4fd8;">
                                    <a href="{{ $url }}"
                                       style="display:inline-block; padding:12px 22px; font-size:15px; font-weight:600; color:#ffffff; text-decoration:none;">
                                        Читать новость на сайте
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 24px; font-size:13px; line-height:1.5; color:#9ca3af; word-break:break-all;">
                            Если кнопка не работает, откройте ссылку: {{ $url }}
                        </p>
                    </td>
                </tr>

                {{-- Подвал --}}
                <tr>
                    <td style="padding:18px 32px 26px; border-top:1px solid #e5e7eb;">
                        <p style="margin:0; font-size:12px; line-height:1.5; color:#9ca3af;">
                            Письмо отправлено автоматически после публикации новости.
                            Управлять новостями и комментариями может только модератор.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
