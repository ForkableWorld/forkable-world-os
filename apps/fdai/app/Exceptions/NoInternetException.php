<?php
namespace App\Exceptions;
use Throwable;
class NoInternetException extends \Exception {
	public function __construct(Throwable $previous = null){
		parent::__construct($previous->getMessage(), 0, $previous);
	}
}
