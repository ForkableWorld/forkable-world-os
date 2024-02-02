<?php

namespace App\Filament\Resources\VariableCategoryResource\Pages;

use App\Filament\Resources\VariableCategoryResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVariableCategories extends ListRecords
{
    protected static string $resource = VariableCategoryResource::class;

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
