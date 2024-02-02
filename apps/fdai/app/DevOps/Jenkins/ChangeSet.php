<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Jenkins;
use App\DevOps\Jenkins\Build\ChangeSet\Item;
use App\Traits\LoggerTrait;
class ChangeSet {
	use LoggerTrait;
	/**
	 * @var Item[]
	 */
	public $items;
	public $affectedPaths;
	public $author;
	public $commitId;
	public $timestamp;
	public $authorEmail;
	public $comment;
	public $date;
	public $id;
	public $msg;
	public $paths;
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
		if(isset($obj->items)){
			foreach($obj->items as $i => $item){
				$this->items[$i] = new Item($item);
			}
		}
	}
	public function getChangedFiles(): array{
		$files = [];
		foreach($this->paths as $file){
			$files[] = new ChangedFile($file);
		}
		return $files;
	}
	/**
	 * @return mixed
	 */
	public function getCommitShaId(): ?string{
		if(!$this->commitId){
			$this->logError("No commit id on " . \App\Logging\QMLog::print_r($this, true));
			return null;
		}
		return $this->commitId;
	}
	public function __toString(){
		return $this->msg;
	}
}
