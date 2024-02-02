<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Reminders;
use App\Variables\QMCommonVariable;
class AnonymousReminder {
	public $variableId;
	public $unitId;
	public $reminderFrequency;
	public $instructions;
	/**
	 * @return int
	 */
	public function getVariableId(): int{
		return $this->variableId;
	}
	/**
	 * @param int $variableId
	 */
	public function setVariableId(int $variableId): void{
		$this->variableId = $variableId;
	}
	/**
	 * @return int
	 */
	public function getUnitId(): ?int{
		return $this->unitId;
	}
	/**
	 * @param int $unitId
	 */
	public function setUnitId(int $unitId): void{
		$this->unitId = $unitId;
	}
	/**
	 * @return string
	 */
	public function getInstructions(): ?string{
		return $this->instructions;
	}
	/**
	 * @param string $instructions
	 */
	public function setInstructions(string $instructions): void{
		$this->instructions = $instructions;
	}
	/**
	 * @return QMCommonVariable
	 */
	public function getCommonVariable(){
		return QMCommonVariable::find($this->getVariableId());
	}
}
