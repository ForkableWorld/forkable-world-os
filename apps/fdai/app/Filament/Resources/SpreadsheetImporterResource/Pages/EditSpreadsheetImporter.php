<?php

namespace App\Filament\Resources\SpreadsheetImporterResource\Pages;

use App\Filament\Resources\SpreadsheetImporterResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpreadsheetImporter extends EditRecord
{
    protected static string $resource = SpreadsheetImporterResource::class;

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
