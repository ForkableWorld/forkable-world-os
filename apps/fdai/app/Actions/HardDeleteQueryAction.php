<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Actions;
class HardDeleteQueryAction extends QueryAction {
	private $reason;
	public function __construct(string $table, array $params, string $reason){
		parent::__construct($table, $params);
		$this->reason = $reason;
	}
	/**
	 * Get the validation rules that apply to the action.
	 * @return array
	 */
	public function rules(){
		$rules = parent::rules();
		$rules['reason'] = ['required'];
		return $rules;
	}
	/**
	 * @inheritDoc
	 */
	public function handle(){
		$qb = $this->getQueryBuilder();
		$qb->hardDelete($this->reason);
	}
}
