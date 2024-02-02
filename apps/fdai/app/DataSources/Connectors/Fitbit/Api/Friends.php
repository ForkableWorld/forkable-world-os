<?php

declare(strict_types=1);

namespace App\DataSources\Connectors\Fitbit\Api;

use App\DataSources\Connectors\Fitbit\Friends\Friends as FriendsOperations;

class Friends
{
    private $friends;

    public function __construct(Fitbit $fitbit)
    {
        $this->friends = new FriendsOperations($fitbit);
    }

    public function friends()
    {
        return $this->friends;
    }
}
