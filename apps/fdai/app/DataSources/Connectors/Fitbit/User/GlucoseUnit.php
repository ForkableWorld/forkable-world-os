<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\User;

use App\DataSources\Connectors\Fitbit\BasicEnum;

class GlucoseUnit extends BasicEnum
{
    public const UNITED_STATES = 'en_US';
    public const INTERNATIONAL = 'any';

    private $glucoseUnit;

    public function __construct(string $glucoseUnit)
    {
        parent::checkValidity($glucoseUnit);
        $this->glucoseUnit = $glucoseUnit;
    }

    public function __toString()
    {
        return $this->glucoseUnit;
    }
}
