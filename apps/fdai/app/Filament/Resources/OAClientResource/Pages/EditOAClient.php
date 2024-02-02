<?php

namespace App\Filament\Resources\OAClientResource\Pages;

use App\Filament\Resources\OAClientResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOAClient extends EditRecord
{
    protected static string $resource = OAClientResource::class;

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
