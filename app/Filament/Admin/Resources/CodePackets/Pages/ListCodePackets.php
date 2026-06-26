<?php

namespace App\Filament\Admin\Resources\CodePackets\Pages;

use App\Filament\Admin\Resources\CodePackets\CodePacketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCodePackets extends ListRecords
{
    protected static string $resource = CodePacketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
