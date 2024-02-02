<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Job\Property\ParameterDefinition;
class DefaultParameterValue {
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $value;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
	}
}
