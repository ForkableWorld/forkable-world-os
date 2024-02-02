<?php

namespace App\Filament\Resources\UserTagResource\Pages;

use App\Filament\Resources\UserTagResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserTags extends ListRecords
{
    protected static string $resource = UserTagResource::class;

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
