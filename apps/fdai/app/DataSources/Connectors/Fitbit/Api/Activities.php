<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Api;

use App\DataSources\Connectors\Fitbit\Activity\Activity;
use App\DataSources\Connectors\Fitbit\Activity\Favorites;
use App\DataSources\Connectors\Fitbit\Activity\Goals\Goals;
use App\DataSources\Connectors\Fitbit\Activity\Intraday;
use App\DataSources\Connectors\Fitbit\Activity\Logs\Logs;
use App\DataSources\Connectors\Fitbit\Activity\TimeSeries;
use App\DataSources\Connectors\Fitbit\Activity\Types;

class Activities
{
    private $activity;
    private $timeSeries;
    private $intraday;
    private $activityTypes;
    private $activityLogs;
    private $favorites;
    private $goals;

    public function __construct(Fitbit $fitbit)
    {
        $this->activity = new Activity($fitbit);
        $this->timeSeries = new TimeSeries($fitbit);
        $this->intraday = new Intraday($fitbit);
        $this->activityTypes = new Types($fitbit);
        $this->activityLogs = new Logs($fitbit);
        $this->favorites = new Favorites($fitbit);
        $this->goals = new Goals($fitbit);
    }

    public function activity()
    {
        return $this->activity;
    }

    public function timeSeries()
    {
        return $this->timeSeries;
    }

    public function intraday()
    {
        return $this->intraday;
    }

    public function activityTypes()
    {
        return $this->activityTypes;
    }

    public function activityLogs()
    {
        return $this->activityLogs;
    }

    public function favorites()
    {
        return $this->favorites;
    }

    public function goals()
    {
        return $this->goals;
    }
}
