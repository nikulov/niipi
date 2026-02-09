<?php

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Facades\Filament;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;

class User extends Authenticatable implements FilamentUser, MustVerifyEmailContract
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, MustVerifyEmailTrait;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $attributes = [
        'role' => 'user',
    ];
    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
    
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
    
    public function sendEmailVerificationNotification(): void
    {
        $panelId = Filament::getCurrentPanel()?->getId() ?? 'admin';
        
        VerifyEmail::createUrlUsing(function ($notifiable) use ($panelId) {
            return URL::temporarySignedRoute(
                "filament.{$panelId}.auth.email-verification.verify",
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });
        
        $this->notify(new VerifyEmail);
    }
}