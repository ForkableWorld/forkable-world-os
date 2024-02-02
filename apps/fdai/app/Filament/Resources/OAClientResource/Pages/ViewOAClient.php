<?php

namespace App\Filament\Resources\OAClientResource\Pages;

use App\Filament\Resources\OAClientResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOAClient extends ViewRecord
{
    protected static string $resource = OAClientResource::class;

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
