<?php

namespace App\Filament\Resources\SpreadsheetImporterResource\Pages;

use App\Filament\Resources\SpreadsheetImporterResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSpreadsheetImporter extends ViewRecord
{
    protected static string $resource = SpreadsheetImporterResource::class;

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
