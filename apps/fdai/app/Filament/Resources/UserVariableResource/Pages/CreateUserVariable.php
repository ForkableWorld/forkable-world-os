<?php

namespace App\Filament\Resources\UserVariableResource\Pages;

use App\Filament\Resources\UserVariableResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserVariable extends CreateRecord
{
    protected static string $resource = UserVariableResource::class;
}
