<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\Bash;
use App\Files\FileLine;
class BashMergedLibFile extends BashScriptFile {
	public function __construct(){
		parent::__construct('scripts/lib/merged_functions.sh');
	}
	public static function merge(){
		$me = new static;
		$sourced = $me->getExistingSourcedScripts();
		foreach($sourced as $libScriptFile){
			$me->addSourcedFunctions($libScriptFile);
		}
	}
	protected function addSourcedFunctions(BashLibScriptFile $libScriptFile){
		$functions = $libScriptFile->getFunctions();
		$path = $libScriptFile->getRelativePath();
		$start = "# START $path";
		$end = "# END $path";
		$this->deleteBetweenAndIncludingStrings($start, $end);
		$this->append($start);
		foreach($functions as $function){
			$str = $function->getReference();
			$this->append($str);
		}
		$this->append($end);
	}
	/**
	 * @return BashLibScriptFile[]
	 */
	protected function getExistingSourcedScripts(): array{
		$scripts = [];
		$lines = $this->getLinesContaining("source ");
		/** @var FileLine[] $lines */
		foreach($lines as $line){
			$path = $line->after('$QM_API/');
			$scripts[] = new BashLibScriptFile($path, $line, $this);
		}
		return $scripts;
	}
}
