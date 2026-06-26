<?php

namespace App\Filament\Admin\Resources\CodePackets\Pages;

use App\Filament\Admin\Resources\CodePackets\CodePacketResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCodePacket extends EditRecord
{
    protected static string $resource = CodePacketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
