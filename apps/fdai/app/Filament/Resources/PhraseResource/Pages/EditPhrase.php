<?php

namespace App\Filament\Resources\PhraseResource\Pages;

use App\Filament\Resources\PhraseResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhrase extends EditRecord
{
    protected static string $resource = PhraseResource::class;

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
