<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\DevOps\Jenkins\Build\ChangeSet;
use App\DevOps\Jenkins\Build\ChangeSet\Item\Author;
use App\DevOps\Jenkins\Build\ChangeSet\Item\Path;
class Item {
	/**
	 * @var string
	 */
	public $_class;
	/**
	 * @var string[]
	 */
	public $affectedPaths;
	/**
	 * @var string
	 */
	public $commitId;
	/**
	 * @var integer
	 */
	public $timestamp;
	/**
	 * @var Author
	 */
	public $author;
	/**
	 * @var string
	 */
	public $authorEmail;
	/**
	 * @var string
	 */
	public $comment;
	/**
	 * @var string
	 */
	public $date;
	/**
	 * @var string
	 */
	public $id;
	/**
	 * @var string
	 */
	public $msg;
	/**
	 * @var Path[]
	 */
	public $paths;
	/**
	 * @param array|object $obj
	 */
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		if(isset($obj->paths)){
			foreach($obj->paths as $i => $item){
				$this->paths[$i] = new Path($item);
			}
		}
	}
}
