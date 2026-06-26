<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->label('Пароль')
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->rule(Password::default())
                    ->autocomplete(false)
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->helperText('Оставьте пустым, чтобы сохранить текущий пароль')
                    ->maxLength(255),

                TextInput::make('callback')
                    ->required()
                    ->default(''),
                DateTimePicker::make('last_query'),
                TextInput::make('source')
                    ->default('bitrix'),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at'),
                TextInput::make('phone')
                    ->tel()
                    ->numeric(),
                TextInput::make('tg_chat_id')
                    ->numeric(),
                TextInput::make('realty_key'),
                TextInput::make('time_delay')
                    ->numeric()
                    ->default(0),
                TextInput::make('utc')
                    ->numeric(),
            ]);
    }
}
