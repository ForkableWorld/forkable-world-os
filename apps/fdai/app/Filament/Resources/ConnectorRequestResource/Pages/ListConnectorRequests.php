<?php

namespace App\Filament\Resources\ConnectorRequestResource\Pages;

use App\Filament\Resources\ConnectorRequestResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConnectorRequests extends ListRecords
{
    protected static string $resource = ConnectorRequestResource::class;

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
