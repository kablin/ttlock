<?php

namespace App\Filament\Admin\Resources\LocksCredentials\Pages;

use App\Filament\Admin\Resources\LocksCredentials\LocksCredentialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocksCredential extends EditRecord
{
    protected static string $resource = LocksCredentialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
