<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Build;
use App\DevOps\Jenkins\Build\Action\Cause;
use App\DevOps\Jenkins\Build\Action\LastBuiltRevision;
class JenkinsBuildAction {
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var Cause[]
	 */
	public $causes;
	/**
	 * @var object
	 */
	public $buildsByBranchName;
	/**
	 * @var LastBuiltRevision
	 */
	public $lastBuiltRevision;
	/**
	 * @var string[]
	 */
	public $remoteUrls;
	/**
	 * @var string
	 */
	public $scmName;
	/**
	 * @var integer
	 */
	public $failCount;
	/**
	 * @var integer
	 */
	public $skipCount;
	/**
	 * @var integer
	 */
	public $totalCount;
	/**
	 * @var string
	 */
	public $urlName;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		if(isset($obj->causes)){
			foreach($obj->causes as $i => $item){
				$this->causes[$i] = new Cause($item);
			}
		}
	}
}
