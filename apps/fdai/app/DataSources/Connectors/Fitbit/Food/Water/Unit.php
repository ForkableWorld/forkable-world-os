<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Food\Water;

use App\DataSources\Connectors\Fitbit\BasicEnum;

class Unit extends BasicEnum
{
    public const MILIMETER = 'ml';
    public const FUILD_OUNCE = 'fl oz';
    public const CUP = 'cup';

    private $unit;

    public function __construct(string $unit)
    {
        parent::checkValidity($unit);
        $this->unit = $unit;
    }

    public function __toString()
    {
        return $this->unit;
    }
}
