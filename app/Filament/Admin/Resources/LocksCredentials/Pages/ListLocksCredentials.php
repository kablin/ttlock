<?php

namespace App\Filament\Admin\Resources\LocksCredentials\Pages;

use App\Filament\Admin\Resources\LocksCredentials\LocksCredentialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLocksCredentials extends ListRecords
{
    protected static string $resource = LocksCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
