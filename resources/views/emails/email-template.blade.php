@php
    $font = "'Century Gothic', 'Carlito', Arial, Helvetica, sans-serif";

    $img = fn (string $file): string => asset('images/email/' . $file);

    $preheader ??= '';
    $date ??= now()->format('d.m.Y');
    $bodyHtml = (string) ($body ?? ($slot ?? ''));

    $phone ??= '8 (495) 242-77-07';
    $email ??= 'niipigrad_niipi@mosreg.ru';
    $siteUrl ??= 'https://niipigrad.ru';
    $address ??= '129110, ул. Гиляровского, д. 47, стр. 3';
    $policyUrl ??= 'https://niipigrad.ru/pd_agreement';

    $legal ??=
        '<p style="margin:0">' .
        'ГАУ МО «НИИПИ градостроительства» ОГРН 1197746272723.</p>' .
        '<p style="margin:0">&copy; ' .
        date('Y') .
        ' Все права защищены.</p>';

    $socials ??= [
        ['icon' => 'social-vk.png', 'url' => '#', 'alt' => 'ВКонтакте'],
        ['icon' => 'social-max.png', 'url' => '#', 'alt' => 'MAX'],
        ['icon' => 'social-tg.png', 'url' => '#', 'alt' => 'Telegram'],
        ['icon' => 'social-rutube.png', 'url' => '#', 'alt' => 'Rutube'],
    ];

    $table = 'border-collapse:collapse;mso-table-lspace:0;mso-table-rspace:0;';
    $image = 'border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;';
@endphp

<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="x-apple-disable-message-reformatting" />
        <meta name="color-scheme" content="light" />
        <meta name="supported-color-schemes" content="light" />
        <title>{{ $title ?? config('app.name') }}</title>

        <style>
            @media only screen and (max-width: 600px) {
                .card-pad {
                    padding: 26px 22px 40px !important;
                }
                .card-foot {
                    padding: 26px 22px !important;
                }
                .stack {
                    display: block !important;
                    width: 100% !important;
                    padding: 4px 0 !important;
                }
            }
        </style>
    </head>

    <body style="margin: 0; padding: 0; width: 100%; background-color: #f4f4f4">
        @if ($preheader !== '')
            <div style="display: none; max-height: 0; overflow: hidden; opacity: 0; mso-hide: all">
                {{ $preheader }}
            </div>
        @endif

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $table }} background-color: #f4f4f4">
            <tr>
                <td align="center" style="padding: 0">
                    <table
                        role="presentation"
                        width="800"
                        cellpadding="0"
                        cellspacing="0"
                        border="0"
                        bgcolor="#f4f4f4"
                        style="
                            {{ $table }}
                            width: 100%;
                            max-width: 800px;
                            background-color: #f4f4f4;
                            background-image: url('{{ $img('wavy_bg.jpg') }}');
                            background-repeat: no-repeat;
                            background-position: top center;
                            background-size: cover;
                        "
                    >
                        <tr>
                            <td align="center" style="padding: 40px 12px 48px">
                                <table
                                    role="presentation"
                                    width="600"
                                    cellpadding="0"
                                    cellspacing="0"
                                    border="0"
                                    align="center"
                                    style="
                                            {{ $table }}
                                            width: 100%;
                                            max-width: 600px;
                                            background-color: #f6f4f4;
                                            box-shadow: 2px 0px 20px 8px rgba(47, 74, 95, 0.12);
                                        "
                                >
                                    <tr>
                                        <td style="padding: 6px">
                                            <img
                                                src="{{ $img('hero.jpg') }}"
                                                width="588"
                                                alt="{{ config('app.name') }}"
                                                style="{{ $image }} display: block; width: 100%; max-width: 588px; height: auto"
                                            />

                                            <table
                                                role="presentation"
                                                width="100%"
                                                cellpadding="0"
                                                cellspacing="0"
                                                border="0"
                                                style="{{ $table }} width: 100%; background-color: #ffffff"
                                            >
                                                <tr>
                                                    <td class="card-pad" style="padding: 36px; font-family: {{ $font }}">
                                                        <div
                                                            style="
                                                                text-align: right;
                                                                color: #2f4a5f;
                                                                font-size: 14px;
                                                                line-height: 1.4;
                                                                opacity: 70%;
                                                            "
                                                        >
                                                            {{ $date }}
                                                        </div>

                                                        <div
                                                            style="
                                                                color: #2f4a5f;
                                                                font-family: {{ $font }};
                                                                font-size: 16px;
                                                                line-height: 1.65;
                                                            "
                                                        >
                                                            {!! $bodyHtml !!}
                                                        </div>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td
                                                        class="card-foot"
                                                        bgcolor="#f6f4f4"
                                                        style="
                                                            padding: 36px 36px 12px 36px;
                                                            background-color: #f6f4f4;
                                                            font-family: {{ $font }};
                                                        "
                                                    >
                                                        <table
                                                            role="presentation"
                                                            cellpadding="0"
                                                            cellspacing="0"
                                                            border="0"
                                                            align="center"
                                                            style="{{ $table }} margin: 0 auto"
                                                        >
                                                            <tr>
                                                                <td class="stack" align="center" style="padding: 0 12px">
                                                                    <a
                                                                        href="tel:{{ preg_replace('/\D+/', '', $phone) }}"
                                                                        style="
                                                                            color: #2f4a5f;
                                                                            font-family: {{ $font }};
                                                                            font-size: 14px;
                                                                            font-weight: 700;
                                                                            line-height: 1.5;
                                                                            text-decoration: none;
                                                                        "
                                                                    >
                                                                        {{ $phone }}
                                                                    </a>
                                                                </td>

                                                                <td class="stack" align="center" style="padding: 0 12px">
                                                                    <a
                                                                        href="mailto:{{ $email }}"
                                                                        style="
                                                                            color: #2f4a5f;
                                                                            font-family: {{ $font }};
                                                                            font-size: 14px;
                                                                            font-weight: 700;
                                                                            line-height: 1.5;
                                                                            text-decoration: none;
                                                                        "
                                                                    >
                                                                        {{ $email }}
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <div
                                                            style="
                                                                padding-top: 10px;
                                                                color: #2f4a5f;
                                                                font-size: 13px;
                                                                line-height: 1.6;
                                                                text-align: center;
                                                            "
                                                        >
                                                            {{ $address }}
                                                        </div>

                                                        @if (! empty($socials))
                                                            <table
                                                                role="presentation"
                                                                cellpadding="0"
                                                                cellspacing="0"
                                                                border="0"
                                                                align="center"
                                                                style="{{ $table }} margin: 14px auto 0"
                                                            >
                                                                <tr>
                                                                    @foreach ($socials as $social)
                                                                        <td style="padding: 0 6px">
                                                                            <a href="{{ $social['url'] }}" style="text-decoration: none">
                                                                                <img
                                                                                    src="{{ $img($social['icon']) }}"
                                                                                    width="36"
                                                                                    height="36"
                                                                                    alt="{{ $social['alt'] ?? '' }}"
                                                                                    style="{{ $image }} display: block; width: 36px; height: 36px"
                                                                                />
                                                                            </a>
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            </table>
                                                        @endif

                                                        <div style="padding-top: 6px; text-align: center">
                                                            <a
                                                                href="{{ $siteUrl }}"
                                                                style="
                                                                    color: #2f4a5f;
                                                                    font-family: {{ $font }};
                                                                    font-size: 14px;
                                                                    font-weight: 700;
                                                                    line-height: 1.5;
                                                                    text-decoration: underline;
                                                                "
                                                            >
                                                                {{ preg_replace('~^https?://~', '', $siteUrl) }}
                                                            </a>
                                                        </div>

                                                        <div style="padding-top: 14px; text-align: center">
                                                            <a
                                                                href="{{ $policyUrl }}"
                                                                style="
                                                                    color: #676767;
                                                                    font-size: 10px;
                                                                    line-height: 1.7;
                                                                    text-align: center;
                                                                    text-decoration: underline;
                                                                "
                                                            >
                                                                Политика обработки персональных данных
                                                            </a>
                                                        </div>

                                                        <div
                                                            style="
                                                                color: #676767;
                                                                font-size: 10px;
                                                                line-height: 1.7;
                                                                text-align: center;
                                                                padding-top: 6px;
                                                            "
                                                        >
                                                            {!! $legal !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
