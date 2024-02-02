<?php

namespace App\Filament\Resources\PatientPhysicianResource\Pages;

use App\Filament\Resources\PatientPhysicianResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPatientPhysician extends ViewRecord
{
    protected static string $resource = PatientPhysicianResource::class;

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
