<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\User;

use App\DataSources\Connectors\Fitbit\BasicEnum;

class TimeFormat extends BasicEnum
{
    public const TWELVE_HOUR = '12hour';
    public const TWENTYFOUR_HOUR = '24hour';

    private $format;

    public function __construct(string $format)
    {
        parent::checkValidity($format);
        $this->format = $format;
    }

    public function __toString()
    {
        return $this->format;
    }
}
