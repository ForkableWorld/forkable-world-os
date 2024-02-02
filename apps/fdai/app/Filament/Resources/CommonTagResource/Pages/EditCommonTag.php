<?php

namespace App\Filament\Resources\CommonTagResource\Pages;

use App\Filament\Resources\CommonTagResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommonTag extends EditRecord
{
    protected static string $resource = CommonTagResource::class;

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
