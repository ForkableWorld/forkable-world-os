<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\User;

use App\DataSources\Connectors\Fitbit\BasicEnum;

class StartDay extends BasicEnum
{
    public const MONDAY = 'Monday';
    public const SUNDAY = 'Sunday';

    private $day;

    public function __construct(string $day)
    {
        parent::checkValidity($day);
        $this->day = $day;
    }

    public function __toString()
    {
        return $this->day;
    }
}
