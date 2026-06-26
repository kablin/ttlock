<?php

namespace App\Filament\Admin\Resources\CodePackets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CodePacketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('end')
                    ->required(),
            ]);
    }
}
