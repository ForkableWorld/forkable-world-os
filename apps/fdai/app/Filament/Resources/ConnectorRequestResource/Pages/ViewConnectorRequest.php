<?php

namespace App\Filament\Resources\ConnectorRequestResource\Pages;

use App\Filament\Resources\ConnectorRequestResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewConnectorRequest extends ViewRecord
{
    protected static string $resource = ConnectorRequestResource::class;

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
