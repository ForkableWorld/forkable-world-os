<?php

namespace App\Filament\Resources\ConnectorImportResource\Pages;

use App\Filament\Resources\ConnectorImportResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewConnectorImport extends ViewRecord
{
    protected static string $resource = ConnectorImportResource::class;

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
