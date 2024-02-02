<?php

namespace App\Filament\Resources\SentEmailResource\Pages;

use App\Filament\Resources\SentEmailResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSentEmail extends ViewRecord
{
    protected static string $resource = SentEmailResource::class;

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
