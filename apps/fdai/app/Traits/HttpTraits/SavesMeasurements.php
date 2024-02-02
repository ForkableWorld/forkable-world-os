<?php

namespace App\Traits\HttpTraits;

use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidVariableValueAttributeException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NoChangesException;
use App\Models\UserVariable;
use App\Slim\Middleware\QMAuth;

trait SavesMeasurements
{
    /**
     * @param $data
     * @return UserVariable[]
     * @throws IncompatibleUnitException
     * @throws InvalidVariableValueAttributeException
     * @throws InvalidVariableValueException
     * @throws ModelValidationException
     * @throws NoChangesException
     */
    protected function saveMeasurements($data): array
    {
        $u = QMAuth::getUser();
        //if(isset($data[0])){$data = $data[0];}
        $measurements = $data['measurements'] ?? $data['measurement_items'] ?? $data;
        if(!isset($measurements[0])){$measurements = [$measurements];}
        if ($measurements) {
            foreach ($measurements as $i => $measurement) {
                $measurement['user_id'] = $u->getId();
                $measurements[$i] = array_merge($data, $measurement);
                unset($measurements[$i]['measurement_items'], $measurements[$i]['measurements']);
            }
            return $u->saveMeasurementFromRequest($measurements);
        }
        return [];
    }
}
