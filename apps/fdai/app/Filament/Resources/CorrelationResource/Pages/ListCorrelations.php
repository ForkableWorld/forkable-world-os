<?php

namespace App\Filament\Resources\CorrelationResource\Pages;

use App\Filament\Resources\CorrelationResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCorrelations extends ListRecords
{
    protected static string $resource = CorrelationResource::class;

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
