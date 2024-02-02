<?php

namespace App\Nfts\Events;

use Illuminate\Database\Eloquent\Model;

class Event
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $tokenize;

    /**
     * Event constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $tokenize
     */
    public function __construct(Model $tokenize)
    {
        $this->tokenize = $tokenize;
    }
}
