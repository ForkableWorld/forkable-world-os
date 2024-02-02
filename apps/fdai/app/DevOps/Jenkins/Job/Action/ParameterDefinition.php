<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Job\Action;
use App\DevOps\Jenkins\Job\Action\ParameterDefinition\DefaultParameterValue;
class ParameterDefinition {
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var DefaultParameterValue
	 */
	public $defaultParameterValue;
	/**
	 * @var string
	 */
	public $description;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $type;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
	}
}
