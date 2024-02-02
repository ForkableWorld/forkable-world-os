<?php

namespace App\Filament\Resources\GlobalVariableRelationshipResource\Pages;

use App\Filament\Resources\GlobalVariableRelationshipResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalVariableRelationship extends EditRecord
{
    protected static string $resource = GlobalVariableRelationshipResource::class;

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
