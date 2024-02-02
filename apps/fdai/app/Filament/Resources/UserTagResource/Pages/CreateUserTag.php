<?php

namespace App\Filament\Resources\UserTagResource\Pages;

use App\Filament\Resources\UserTagResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserTag extends CreateRecord
{
    protected static string $resource = UserTagResource::class;
}
