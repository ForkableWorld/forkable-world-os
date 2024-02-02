<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Exceptions\NotEnoughDataException;
use App\Models\CtConditionCause;
use App\Models\Variable;
use App\UI\FontAwesome;
abstract class ConditionCauseTable extends VariableRelationsTable {
	/**
	 * @param Variable $variable
	 * @throws NotEnoughDataException
	 */
	public function __construct($variable){
		parent::__construct($variable);
		$this->addPercentColumn();
	}
	public function addPercentColumn(){
		$this->column()->title('Agree ' . FontAwesome::html(FontAwesome::QUESTION_CIRCLE) . " ")
			->attr('th', 'title', $this->agreeTooltip())->value(function($cc){
				/** @var CtConditionCause $cc */
				$change = $cc->votes_percent;
				return $change . "%";
			})->attr('td', 'data-order', function($cc){
				/** @var CtConditionCause $cc */
				return $cc->votes_percent;
			})->add();
	}
	abstract protected function agreeTooltip(): string;
}
