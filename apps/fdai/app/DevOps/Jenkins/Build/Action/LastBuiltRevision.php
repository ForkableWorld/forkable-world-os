<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Build\Action;
class LastBuiltRevision {
	/**
	 * @var string
	 */
	public $SHA1;
	/**
	 * @var Branch[]
	 */
	public $branch;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		if(isset($obj->branch)){
			foreach($obj->branch as $i => $item){
				$this->branch[$i] = new Branch($item);
			}
		}
	}
}
