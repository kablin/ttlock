<?php

namespace App\Filament\Admin\Resources\LocksCredentials;

use App\Filament\Admin\Resources\LocksCredentials\Pages\CreateLocksCredential;
use App\Filament\Admin\Resources\LocksCredentials\Pages\EditLocksCredential;
use App\Filament\Admin\Resources\LocksCredentials\Pages\ListLocksCredentials;
use App\Filament\Admin\Resources\LocksCredentials\Schemas\LocksCredentialForm;
use App\Filament\Admin\Resources\LocksCredentials\Tables\LocksCredentialsTable;
use App\Models\LocksCredential;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LocksCredentialResource extends Resource
{
    protected static ?string $model = LocksCredential::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'login';

    public static function form(Schema $schema): Schema
    {
        return LocksCredentialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocksCredentialsTable::configure($table);
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
            'index' => ListLocksCredentials::route('/'),
            'create' => CreateLocksCredential::route('/create'),
            'edit' => EditLocksCredential::route('/{record}/edit'),
        ];
    }
}
