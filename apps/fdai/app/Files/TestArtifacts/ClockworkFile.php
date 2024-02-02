<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use App\Files\Json\JsonFile;
use App\Files\TypedProjectFile;
use App\Folders\ClockworkFolder;
use App\Logging\QMClockwork;
use App\Logging\QMLog;
use App\Utils\EnvOverride;
class ClockworkFile extends JsonFile {
	use IsTestArtifactFile;
	public static function getData(): ?TypedProjectFile{
		QMClockwork::saveResults();
		$file = ClockworkFolder::get()->getNewestFile(true, ".json");
		return $file;
	}
	public static function saveIfLocal(){
		if(!EnvOverride::isLocal()){
			return;
		}
		QMLog::info(__METHOD__);
		self::savePropertiesAsSeparateFiles();
	}
}
