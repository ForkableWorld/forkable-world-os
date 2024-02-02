<?php

namespace App\Filament\Resources\ConnectorResource\Pages;

use App\Filament\Resources\ConnectorResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConnectors extends ListRecords
{
    protected static string $resource = ConnectorResource::class;

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
