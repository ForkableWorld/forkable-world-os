<?php

namespace App\Filament\Resources\OAAccessTokenResource\Pages;

use App\Filament\Resources\OAAccessTokenResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOAAccessToken extends ViewRecord
{
    protected static string $resource = OAAccessTokenResource::class;

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
