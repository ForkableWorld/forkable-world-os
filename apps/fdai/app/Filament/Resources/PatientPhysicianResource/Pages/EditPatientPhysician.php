<?php

namespace App\Filament\Resources\PatientPhysicianResource\Pages;

use App\Filament\Resources\PatientPhysicianResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatientPhysician extends EditRecord
{
    protected static string $resource = PatientPhysicianResource::class;

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
