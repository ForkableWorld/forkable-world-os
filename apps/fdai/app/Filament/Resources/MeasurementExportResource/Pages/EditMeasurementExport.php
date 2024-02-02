<?php

namespace App\Filament\Resources\MeasurementExportResource\Pages;

use App\Filament\Resources\MeasurementExportResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeasurementExport extends EditRecord
{
    protected static string $resource = MeasurementExportResource::class;

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
