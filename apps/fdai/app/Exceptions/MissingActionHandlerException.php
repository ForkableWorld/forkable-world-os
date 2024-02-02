<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;

use Exception;

class MissingActionHandlerException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param  mixed  $action
     * @param  string  $method
     * @return static
     */
    public static function make($action, $method)
    {
        return new static('Action handler ['.get_class($action).'@'.$method.'] not defined.');
    }
}
