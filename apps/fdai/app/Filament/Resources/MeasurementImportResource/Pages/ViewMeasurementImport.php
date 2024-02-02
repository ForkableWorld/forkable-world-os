<?php

namespace App\Filament\Resources\MeasurementImportResource\Pages;

use App\Filament\Resources\MeasurementImportResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMeasurementImport extends ViewRecord
{
    protected static string $resource = MeasurementImportResource::class;

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
