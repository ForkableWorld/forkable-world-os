<?php

namespace App\Filament\Resources\CommonTagResource\Pages;

use App\Filament\Resources\CommonTagResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommonTag extends ViewRecord
{
    protected static string $resource = CommonTagResource::class;

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
