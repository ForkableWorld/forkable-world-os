<?php

namespace App\Filament\Resources\MeasurementExportResource\Pages;

use App\Filament\Resources\MeasurementExportResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMeasurementExport extends ViewRecord
{
    protected static string $resource = MeasurementExportResource::class;

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
