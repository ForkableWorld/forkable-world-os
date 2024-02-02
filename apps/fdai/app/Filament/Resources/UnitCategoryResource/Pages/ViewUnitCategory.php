<?php

namespace App\Filament\Resources\UnitCategoryResource\Pages;

use App\Filament\Resources\UnitCategoryResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUnitCategory extends ViewRecord
{
    protected static string $resource = UnitCategoryResource::class;

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
