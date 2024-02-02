<?php
namespace App\Http\Controllers;
use App\Exceptions\BadRequestException;
class WrongParameterException extends BadRequestException
{
    /**
     * @param string $class
     * @param array|null $nonExistent
     * @param array|null $fillable
     */
    public function __construct(string $class, array $nonExistent = null, array $fillable = null){
        $msg = "The following attributes do not exist on $class:\n - " . implode("\n - ", $nonExistent).
            ".\n\n Available attributes are:\n - ".implode("\n - ", $fillable);
        parent::__construct($msg);
    }
}
