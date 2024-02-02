<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasProperty;
use App\Types\TimeHelper;
trait HasOnsetAndDuration {
	/**
	 * @return string
	 */
	protected function getOnsetDelayDurationOfActionString(): string{
		$c = $this->getHasCorrelationCoefficient();
		return ' onset delay = ' . $c->getOnsetDelayHumanString() . ', duration of action = ' .
			$c->getDurationOfActionHumanString();
	}
	public function getOnsetDelayDurationHumanString(): string{
		return $this->getOnsetDelayHumanString() . " onset delay and " . $this->getDurationOfActionHumanString() .
			" duration of action";
	}
	/**
	 * @return string
	 */
	public function getOnsetDelayHumanString(): string{
		$delay = TimeHelper::convertSecondsToHumanString($this->getOnsetDelay());
		return $delay;
	}
	abstract public function getOnsetDelay(): int;
	/**
	 * @return int
	 */
	public function getOnsetDelayInHours(): int{
		$onsetDelay = round($this->getOnsetDelay() / 3600, 1);
		return $onsetDelay;
	}
	/**
	 * @return string
	 */
	public function getDurationOfActionHumanString(): string{
		$duration = TimeHelper::convertSecondsToHumanString($this->getDurationOfAction());
		return $duration;
	}
	/**
	 * @return int
	 */
	public function getDurationOfActionInHours(): int{
		$duration = round($this->getDurationOfAction() / 3600, 1);
		return $duration;
	}
	/**
	 * @return float
	 */
	public function getDurationOfActionInDays(): float{
		return $this->getDurationOfAction() / 86400;
	}
	abstract public function getDurationOfAction(): int;
}
