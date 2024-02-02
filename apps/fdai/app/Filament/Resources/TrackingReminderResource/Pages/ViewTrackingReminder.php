<?php

namespace App\Filament\Resources\TrackingReminderResource\Pages;

use App\Filament\Resources\TrackingReminderResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTrackingReminder extends ViewRecord
{
    protected static string $resource = TrackingReminderResource::class;

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
