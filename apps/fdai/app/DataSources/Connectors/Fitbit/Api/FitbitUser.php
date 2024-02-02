<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Api;

use App\DataSources\Connectors\Fitbit\User\User as UserOperations;

class FitbitUser
{
    private $user;

    public function __construct(Fitbit $fitbit)
    {
        $this->user = new UserOperations($fitbit);
    }

    public function user()
    {
        return $this->user;
    }
}
