<?php

namespace App\Filament\Admin\Resources\CodePackets;

use App\Filament\Admin\Resources\CodePackets\Pages\CreateCodePacket;
use App\Filament\Admin\Resources\CodePackets\Pages\EditCodePacket;
use App\Filament\Admin\Resources\CodePackets\Pages\ListCodePackets;
use App\Filament\Admin\Resources\CodePackets\Schemas\CodePacketForm;
use App\Filament\Admin\Resources\CodePackets\Tables\CodePacketsTable;
use App\Models\CodePacket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CodePacketResource extends Resource
{
    protected static ?string $model = CodePacket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CodePacketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CodePacketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCodePackets::route('/'),
            'create' => CreateCodePacket::route('/create'),
            'edit' => EditCodePacket::route('/{record}/edit'),
        ];
    }
}
