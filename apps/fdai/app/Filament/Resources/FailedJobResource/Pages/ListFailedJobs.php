<?php

namespace App\Filament\Resources\FailedJobResource\Pages;

use App\Filament\Resources\FailedJobResource;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFailedJobs extends ListRecords
{
    protected static string $resource = FailedJobResource::class;
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
