<?php

namespace App\Filament\Resources\NftResource\Pages;

use App\Filament\Resources\NftResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNft extends ViewRecord
{
    protected static string $resource = NftResource::class;

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
