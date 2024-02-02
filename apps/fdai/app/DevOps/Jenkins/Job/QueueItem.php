<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Job;
use App\DevOps\Jenkins\Job\QueueItem\Task;
class QueueItem {
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var boolean
	 */
	public $blocked;
	/**
	 * @var boolean
	 */
	public $buildable;
	/**
	 * @var integer
	 */
	public $id;
	/**
	 * @var integer
	 */
	public $inQueueSince;
	/**
	 * @var string
	 */
	public $params;
	/**
	 * @var boolean
	 */
	public $stuck;
	/**
	 * @var Task
	 */
	public $task;
	/**
	 * @var string
	 */
	public $url;
	/**
	 * @var string
	 */
	public $why;
	/**
	 * @var integer
	 */
	public $buildableStartMilliseconds;
	/**
	 * @var boolean
	 */
	public $pending;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
	}
}
