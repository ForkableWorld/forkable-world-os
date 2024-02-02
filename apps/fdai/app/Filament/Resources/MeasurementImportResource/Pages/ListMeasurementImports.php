<?php

namespace App\Filament\Resources\MeasurementImportResource\Pages;

use App\Filament\Resources\MeasurementImportResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeasurementImports extends ListRecords
{
    protected static string $resource = MeasurementImportResource::class;

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
