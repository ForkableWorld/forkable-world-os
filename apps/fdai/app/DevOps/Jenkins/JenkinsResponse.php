<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins;
use App\DevOps\Jenkins\JenkinsResponse\AssignedLabel;
class JenkinsResponse {
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var AssignedLabel[]
	 */
	public $assignedLabels;
	/**
	 * @var string
	 */
	public $mode;
	/**
	 * @var string
	 */
	public $nodeDescription;
	/**
	 * @var string
	 */
	public $nodeName;
	/**
	 * @var integer
	 */
	public $numExecutors;
	/**
	 * @var NULL
	 */
	public $description;
	/**
	 * @var JenkinsJob[]
	 */
	public $jobs;
	/**
	 * @var object
	 */
	public $overallLoad;
	/**
	 * @var JenkinsView
	 */
	public $primaryView;
	/**
	 * @var boolean
	 */
	public $quietingDown;
	/**
	 * @var integer
	 */
	public $slaveAgentPort;
	/**
	 * @var object
	 */
	public $unlabeledLoad;
	/**
	 * @var string
	 */
	public $url;
	/**
	 * @var boolean
	 */
	public $useCrumbs;
	/**
	 * @var boolean
	 */
	public $useSecurity;
	/**
	 * @var JenkinsView[]
	 */
	public $views;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		if(isset($obj->assignedLabels)){
			foreach($obj->assignedLabels as $i => $item){
				$this->assignedLabels[$i] = new AssignedLabel($item);
			}
		}
		if(isset($obj->jobs)){
			foreach($obj->jobs as $i => $item){
				$this->jobs[$i] = new JenkinsJob($item);
			}
		}
		if(isset($obj->views)){
			foreach($obj->views as $i => $item){
				$this->views[$i] = new JenkinsView($item);
			}
		}
	}
}
