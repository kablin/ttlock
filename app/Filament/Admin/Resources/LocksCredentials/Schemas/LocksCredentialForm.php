<?php

namespace App\Filament\Admin\Resources\LocksCredentials\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LocksCredentialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('login')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
            ]);
    }
}
