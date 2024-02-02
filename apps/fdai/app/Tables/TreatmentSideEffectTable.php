<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Exceptions\NotEnoughDataException;
use App\Models\Variable;
abstract class TreatmentSideEffectTable extends VariableRelationsTable {
	/**
	 * @param Variable $variable
	 * @throws NotEnoughDataException
	 */
	public function __construct($variable){
		parent::__construct($variable);
		$this->addValueColumn();
	}
	abstract protected function addValueColumn();
}
