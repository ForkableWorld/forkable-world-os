<?php

namespace App\Filament\Resources\MeasurementImportResource\Pages;

use App\Filament\Resources\MeasurementImportResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeasurementImport extends EditRecord
{
    protected static string $resource = MeasurementImportResource::class;

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
