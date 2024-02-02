<?php

namespace App\Filament\Resources\DeviceTokenResource\Pages;

use App\Filament\Resources\DeviceTokenResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDeviceToken extends ViewRecord
{
    protected static string $resource = DeviceTokenResource::class;
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
