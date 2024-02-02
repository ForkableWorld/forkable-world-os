<?php

namespace App\Filament\Resources\IpDatumResource\Pages;

use App\Filament\Resources\IpDatumResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIpDatum extends ViewRecord
{
    protected static string $resource = IpDatumResource::class;

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
