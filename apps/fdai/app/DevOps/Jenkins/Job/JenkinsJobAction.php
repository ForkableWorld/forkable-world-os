<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Job;
use App\DevOps\Jenkins\Job\Action\ParameterDefinition;
class JenkinsJobAction {
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var ParameterDefinition[]
	 */
	public $parameterDefinitions;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		if(isset($obj->parameterDefinitions)){
			foreach($obj->parameterDefinitions as $i => $item){
				$this->parameterDefinitions[$i] = new ParameterDefinition($item);
			}
		}
	}
}
