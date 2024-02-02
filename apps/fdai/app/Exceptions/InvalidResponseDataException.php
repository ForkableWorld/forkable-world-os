<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;
class InvalidResponseDataException extends Exception
{
    protected $data;
    public function __construct(string $message, $data){
        $this->data = $data;
        parent::__construct($message);
    }
}
