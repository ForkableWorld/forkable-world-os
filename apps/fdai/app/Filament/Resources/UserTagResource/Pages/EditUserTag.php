<?php

namespace App\Filament\Resources\UserTagResource\Pages;

use App\Filament\Resources\UserTagResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserTag extends EditRecord
{
    protected static string $resource = UserTagResource::class;

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
