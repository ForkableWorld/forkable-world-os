<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Build\ChangeSet\Item;
class Path {
	/**
	 * @var string
	 */
	public $editType;
	/**
	 * @var string
	 */
	public $file;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
	}
}
