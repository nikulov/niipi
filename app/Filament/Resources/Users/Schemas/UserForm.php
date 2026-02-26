<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                Group::make()->schema([
                    
                    TextInput::make('name')->label(__('panel.user_name'))
                        ->trim()
                        ->required(),
                    
                    TextInput::make('email')->label(__('panel.email'))
                        ->email()
                        ->trim()
                        ->unique()
                        ->disabled(fn(string $operation): bool => $operation !== 'create')
                        ->required(),
                    
                    TextInput::make('password')->label(__('panel.password'))
                        ->password()
                        ->revealable()
                        ->trim()
                        ->live()
                        ->required(fn(string $operation): bool => $operation === 'create')
                        ->dehydrated(fn(?string $state): bool => is_string($state) && $state !== '')
                        ->dehydrateStateUsing(fn(?string $state): ?string => is_string($state) && $state !== ''
                            ? Hash::make($state)
                            : null
                        )
                        ->live()
                        ->helperText(function (Get $get, string $operation): ?string {
                            $hasPassword = is_string($get('password')) && $get('password') !== '';
                            
                            if ($hasPassword) {
                                return __('panel.password_requirements_short'); // когда начали вводить
                            }
                            
                            if ($operation === 'edit') {
                                return __('panel.leave_blank_to_keep_password'); // когда пусто на edit
                            }
                            
                            return __('panel.password_requirements_short'); // create и пусто
                        })
                        ->rule(function (?string $state) {
                            if (!is_string($state) || $state === '') {
                                return null;
                            }
                            
                            return PasswordRule::min(8)
                                ->mixedCase()
                                ->letters()
                                ->numbers()
                                ->symbols()
                                ->uncompromised();
                        }),
                    
                    TextInput::make('password_confirmation')->label(__('panel.password_confirmation'))
                        ->password()
                        ->revealable()
                        ->trim()
                        ->visible(fn (Get $get): bool => filled($get('password')))
                        ->same('password')
                        ->requiredWith('password')
                        ->dehydrated(false),
                
                ]),
                
                Group::make()
                    ->schema([
                        
                        Select::make('role')->label(__('panel.role'))
                            ->options(UserRole::options())
                            ->default(UserRole::Viewer->value)
                            ->required(),
                        
                        DateTimePicker::make('email_verified_at')->label(__('panel.email_verified_at'))
                            ->disabled()
                            ->prefixIcon(Heroicon::Check)
                            ->prefixIconColor('success')
                            ->visible(fn($record): bool => filled($record?->email_verified_at)),
                        
                        
                        Action::make('sendVerificationEmail')->label(__('panel.send_verification_email'))
                            ->button()
                            ->extraAttributes(['style' => 'margin-top: 28px;'])
                            ->color('warning')
                            ->visible(fn($record): bool => $record && blank($record->email_verified_at))
                            ->requiresConfirmation()
                            ->action(function ($record): void {
                                if (!$record) {
                                    return;
                                }
                                
                                try {
                                    $record->sendEmailVerificationNotification();
                                    
                                    Notification::make()->title(__('panel.verification_email_sent'))
                                        ->success()
                                        ->send();
                                } catch (\Throwable $e) {
                                    Notification::make()->title(__('panel.verification_email_failed'))
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                    ]),
            ]);
    }
}
