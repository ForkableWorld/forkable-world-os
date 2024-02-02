<?php

namespace App\Filament\Resources\OAAccessTokenResource\Pages;

use App\Filament\Resources\OAAccessTokenResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOAAccessToken extends EditRecord
{
    protected static string $resource = OAAccessTokenResource::class;

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
