<?php

namespace App\Filament\Resources\WpUsermetumResource\Pages;

use App\Filament\Resources\WpUsermetumResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWpUsermeta extends ListRecords
{
    protected static string $resource = WpUsermetumResource::class;

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
