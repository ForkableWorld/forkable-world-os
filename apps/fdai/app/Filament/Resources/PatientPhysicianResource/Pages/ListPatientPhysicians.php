<?php

namespace App\Filament\Resources\PatientPhysicianResource\Pages;

use App\Filament\Resources\PatientPhysicianResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatientPhysicians extends ListRecords
{
    protected static string $resource = PatientPhysicianResource::class;

    /**
     * @throws Exception
     */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
