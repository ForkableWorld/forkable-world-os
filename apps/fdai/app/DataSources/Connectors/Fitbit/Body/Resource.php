<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Body;

use App\DataSources\Connectors\Fitbit\BasicEnum;

class Resource extends BasicEnum
{
    const BMI = 'bmi';
    const FAT = 'fat';
    const WEIGHT = 'weight';

    private $resource;

    public function __construct(string $resource)
    {
        parent::checkValidity($resource);
        $this->resource = $resource;
    }

    public function __toString()
    {
        return $this->resource;
    }
}
