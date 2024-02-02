<?php

namespace App\Filament\Resources\PhraseResource\Pages;

use App\Filament\Resources\PhraseResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPhrase extends ViewRecord
{
    protected static string $resource = PhraseResource::class;

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
