<?php

namespace App\Filament\Resources\VariableCategoryResource\Pages;

use App\Filament\Resources\VariableCategoryResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVariableCategory extends EditRecord
{
    protected static string $resource = VariableCategoryResource::class;

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
