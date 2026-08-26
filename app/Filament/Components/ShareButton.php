<?php

namespace App\Filament\Components;

use App\Filament\Forms\Components\CustomRepeater;
use App\Filament\Forms\Components\MediaPickerAction;
use App\Filament\Forms\Components\UrlInput;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

final class ShareButton
{
    public static function key(): string
    {
        return 'share-button';
    }

    /** Build Filament Block */
    public static function block(): Block
    {
        return Block::make(self::key())->label(__('panel.share_btn'))
            ->columnSpanFull()
            ->schema([

                TextInput::make('btnLabel')->label(__(key: 'panel.btn_label'))
                    ->required()
                    ->columnSpan(12),

                UrlInput::make('btnUrl')->label(__(key: 'panel.btn_url'))
                    ->columnSpan(12)
                    ->required(),

                Select::make('btnType')->label(__(key: 'panel.type'))
                    ->options([
                        'btn-primary' => __('panel.primary'),
                        'btn-secondary' => __('panel.secondary'),
                        'btn-accent' => __('panel.accent'),
                        'btn-transparent' => __('panel.accent_additional'),
                    ])
                    ->required()
                    ->columnSpan(8),

                Select::make('btnPosition')->label(__(key: 'panel.position'))
                    ->options([
                        'start' => __('panel.left'),
                        'center' => __('panel.center'),
                        'end' => __('panel.right'),
                    ])
                    ->required()
                    ->columnSpan(8),

                Toggle::make('blank')->label(__(key: 'panel.open_page_in_new_tab'))
                    ->inline(false)
                    ->default(false)
                    ->columnSpan(4),

                Toggle::make('showCopy')->label(__(key: 'panel.show_copy_link'))
                    ->inline(false)
                    ->default(true)
                    ->columnSpan(4),

                CustomRepeater::make('socials')->label(__(key: 'panel.social'))
                    ->deleteAction(
                        fn (Action $action) => $action->requiresConfirmation(),
                    )
                    ->maxItems(5)
                    ->itemLabel(fn (array $state): string => $state['title'] ?? __('panel.social_icon'))
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->addActionLabel(__(key: 'panel.add_social_icon'))
                    ->default(self::defaultSocialsAsFormState())
                    ->columnSpanFull()
                    ->columns(24)
                    ->collapsed()
                    ->reorderable()
                    ->schema([

                        FileUpload::make('iconUrl')->label(__(key: 'panel.icon').' ('.__('panel.svg').')')
                            ->columnSpan(6)
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->moveFiles()
                            ->disk('public')
                            ->directory('images/icon')
                            ->visibility('public')
                            ->image()
                            ->acceptedFileTypes(['image/svg+xml'])
                            ->required()
                            ->hintAction(MediaPickerAction::make('iconUrl', acceptedMimeTypes: ['image/svg+xml'])),

                        TextInput::make('title')->label(__(key: 'panel.title'))
                            ->columnSpan(6)
                            ->trim()
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('shareUrl')->label(__(key: 'panel.share_url'))
                            ->helperText(__('panel.share_url_hint'))
                            ->columnSpan(12)
                            ->trim()
                            ->maxLength(255)
                            ->required(),

                    ]),

            ])->columns(24);
    }

    /** Default block for Post/Project: share bar plus a button back to the section index. */
    public static function getDefaultBlock(string $btnUrl, string $btnLabel): array
    {
        return [
            [
                'type' => self::key(),
                'data' => [
                    'btnLabel' => $btnLabel,
                    'btnUrl' => $btnUrl,
                    'btnType' => 'btn-primary',
                    'btnPosition' => 'end',
                    'blank' => false,
                    'showCopy' => true,
                    'socials' => self::defaultSocials(),
                ],
            ],
        ];
    }

    /**
     * The same set in the shape a `FileUpload` keeps while the form is open.
     *
     * `hydrateDefaultState()` writes a default straight into raw state without running state casts
     * (`HasState.php:481`), and `FileUpload` keeps raw state as an array of paths — a bare string
     * there breaks `getUploadedFiles()` and the file validation rule. Saving turns it back into the
     * string `defaultSocials()` returns (`FileUploadStateCast::get()` → `Arr::first()`).
     *
     * @return array<int, array{iconUrl: array<int, string>, title: string, shareUrl: string}>
     */
    public static function defaultSocialsAsFormState(): array
    {
        return array_map(
            fn (array $social): array => [...$social, 'iconUrl' => [$social['iconUrl']]],
            self::defaultSocials(),
        );
    }

    /**
     * Share targets the block starts with — the set that used to be hardcoded in the template.
     *
     * This is the stored shape: what a saved block holds in its JSON column and what the template
     * reads. For the shape the open form needs, see `defaultSocialsAsFormState()`.
     *
     * `shareUrl` is a template, not a finished address: the `{url}` / `{title}` placeholders are
     * filled in by the browser, so the block shares whichever page it sits on.
     *
     * Icon paths are relative to the `public` disk (`storage/app/public`), the one the repeater's
     * `FileUpload` writes to. Filament drops paths that are missing from that disk
     * (`BaseFileUpload::afterStateHydrated`), so on a fresh environment the three files have to be
     * uploaded through the media library before the default survives a save.
     *
     * @return array<int, array{iconUrl: string, title: string, shareUrl: string}>
     */
    public static function defaultSocials(): array
    {
        return [
            [
                'iconUrl' => 'images/icon/vk.svg',
                'title' => 'ВКонтакте',
                'shareUrl' => 'https://vk.com/share.php?url={url}&title={title}',
            ],
            [
                'iconUrl' => 'images/icon/telegram.svg',
                'title' => 'Telegram',
                'shareUrl' => 'https://t.me/share/url?url={url}&text={title}',
            ],
            [
                'iconUrl' => 'images/icon/max.svg',
                'title' => 'MAX',
                'shareUrl' => 'https://max.ru/:share?text={url}',
            ],
        ];
    }
}
