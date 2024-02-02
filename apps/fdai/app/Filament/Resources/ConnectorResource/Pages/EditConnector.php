<?php

namespace App\Filament\Resources\ConnectorResource\Pages;

use App\Filament\Resources\ConnectorResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConnector extends EditRecord
{
    protected static string $resource = ConnectorResource::class;

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
