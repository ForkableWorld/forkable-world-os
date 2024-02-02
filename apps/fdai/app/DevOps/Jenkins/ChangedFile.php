<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Jenkins;
use App\Files\FileHelper;
class ChangedFile {
	public const EDIT_TYPE_EDIT = "edit";
	public $editType;
	public $file;
	public function __construct($obj){
		foreach($obj as $key => $value){
			$this->$key = $value;
		}
	}
	public function getAbsolutePath(): string{
		return FileHelper::absPath($this->getRelativePath());
	}
	public function getRelativePath(): string{
		return $this->file;
	}
}
