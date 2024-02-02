<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class TooBigForMySQLException extends Exception implements ProvidesSolution
{
    protected $value;
    /**
     * @var string
     */
    public $s3Path;
    /**
     * @var AnalyzeSizeSolution
     */
    protected $solution;
    public function __construct(string $message, string $s3Path, $value){
        $this->value = $value;
        $this->s3Path = $s3Path;
        parent::__construct($message);
    }
    public function getSolution(): Solution {
        if($this->solution){return $this->solution;}
        return $this->solution = new AnalyzeSizeSolution($this->s3Path, $this->value);
    }
}
