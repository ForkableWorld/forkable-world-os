<?php
namespace App\Exceptions;

class NotFillableException extends BadRequestException
{
    /**
     * @param string $class
     * @param array $doNotExist
     * @param array $fillable
     */
    public function __construct(string $class, array $doNotExist, array $fillable)
    {
        parent::__construct("The following properties are not modifiable on $class:\n" .
            implode(", ", array_keys($doNotExist)), $doNotExist).
        ".\nAvailable properties are: \n".implode("\n", $fillable);
    }
}
