<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
class QMFileNotFoundException extends \Exception
{
    public function __construct(string $filepath, string $message = ""){
        parent::__construct("File not found at:\n\t$filepath\n$message");
    }
}
