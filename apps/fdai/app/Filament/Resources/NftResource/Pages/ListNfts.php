<?php

namespace App\Filament\Resources\NftResource\Pages;

use App\Filament\Resources\NftResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNfts extends ListRecords
{
    protected static string $resource = NftResource::class;

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
