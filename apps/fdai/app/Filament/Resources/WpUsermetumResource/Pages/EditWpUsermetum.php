<?php

namespace App\Filament\Resources\WpUsermetumResource\Pages;

use App\Filament\Resources\WpUsermetumResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWpUsermetum extends EditRecord
{
    protected static string $resource = WpUsermetumResource::class;

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
