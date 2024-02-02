<?php

namespace App\Filament\Resources\WpUsermetumResource\Pages;

use App\Filament\Resources\WpUsermetumResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWpUsermetum extends ViewRecord
{
    protected static string $resource = WpUsermetumResource::class;

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
