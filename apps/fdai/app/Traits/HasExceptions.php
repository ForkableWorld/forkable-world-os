<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Cards\ErrorCard;
use App\Exceptions\ExceptionHandler;
use Throwable;
trait HasExceptions {
	protected $exceptions = [];
	public function addException(Throwable $e): void{
		$this->exceptions[] = $e;
	}
	/**
	 * @return Throwable[]
	 */
	public function getExceptions(): array{
		return $this->exceptions;
	}
	/**
	 * @return ErrorCard[]
	 */
	public function getExceptionCards(): array{
		$exceptions = $this->exceptions;
		$cards = ExceptionHandler::toExceptionCards($exceptions);
		return $cards;
	}
}
