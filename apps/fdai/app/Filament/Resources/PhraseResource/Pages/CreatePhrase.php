<?php

namespace App\Filament\Resources\PhraseResource\Pages;

use App\Filament\Resources\PhraseResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePhrase extends CreateRecord
{
    protected static string $resource = PhraseResource::class;
}
