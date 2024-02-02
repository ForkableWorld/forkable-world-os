<?php

namespace App\Filament\Resources\UserVariableResource\Pages;

use App\Filament\Resources\UserVariableResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserVariable extends EditRecord
{
    protected static string $resource = UserVariableResource::class;

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
