<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Actions;
use App\Slim\Middleware\QMAuth;
use App\Variables\QMCommonVariable;
class DeleteSmallMeasurementsAction extends AdminAction {
	private $variable_id;
	public function __construct(){
		parent::__construct();
	}
	/**
	 * Determine if the user is authorized to make this action.
	 * @return bool
	 */
	public function authorize(){
		return QMAuth::isAdmin();
	}
	/**
	 * Get the validation rules that apply to the action.
	 * @return array
	 */
	public function rules(){
		return [
			'variable_id' => ['required'],
		];
	}
	/**
	 * Execute the action and return a result.
	 * @return mixed
	 */
	public function handle(){
		$variable = QMCommonVariable::find($this->variable_id);
		return $variable->deleteSmallMeasurements();
	}
	public function response($result){
		$variable = QMCommonVariable::find($this->variable_id);
		return "Deleted $result $variable->name measurements below min";
	}
}
