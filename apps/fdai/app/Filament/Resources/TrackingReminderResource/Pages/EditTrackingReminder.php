<?php

namespace App\Filament\Resources\TrackingReminderResource\Pages;

use App\Filament\Resources\TrackingReminderResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrackingReminder extends EditRecord
{
    protected static string $resource = TrackingReminderResource::class;

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
