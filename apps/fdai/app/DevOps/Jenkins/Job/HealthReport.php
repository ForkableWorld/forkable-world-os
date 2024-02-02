<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Job;
class HealthReport {
	/**
	 * @var string
	 */
	public $description;
	/**
	 * @var string
	 */
	public $iconClassName;
	/**
	 * @var string
	 */
	public $iconUrl;
	/**
	 * @var integer
	 */
	public $score;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
	}
}
