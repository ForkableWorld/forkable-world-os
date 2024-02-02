<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\FileTraits;
trait IsFormattableFile {
	public static function reformatAll(): void{
		/** @var static[] $all */
		$all = static::get();
		foreach($all as $file){
			try {
				$file->reformat();
			} catch (\Throwable $e) {
				$file->logError(__METHOD__.": ".$e->getMessage());
			}
		}
	}
	abstract public function reformat(): void;
	public function trimRightSideOfLines(){
		$this->getFileLines(); // Save applies rtrim to all lines
		$this->save();
	}
}
