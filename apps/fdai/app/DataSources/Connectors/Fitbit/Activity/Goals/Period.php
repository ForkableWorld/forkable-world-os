<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Activity\Goals;

use App\DataSources\Connectors\Fitbit\BasicEnum;

class Period extends BasicEnum
{
    const DAILY = 'daily';
    const WEEKLY = 'weekly';

    private $period;

    public function __construct(string $period)
    {
        parent::checkValidity($period);
        $this->period = $period;
    }

    public function __toString()
    {
        return $this->period;
    }
}
