<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Files\TestArtifacts;
use App\Files\Json\JsonFile;
use Tests\QMDebugBar;
class AbstractDebugbarFile extends JsonFile {
	use IsTestArtifactFile;
	/**
	 * @return array|string
	 */
	public static function getData(){
		return QMDebugBar::getCollectorData();
	}
	public static function saveAllIfLocal(){
		//parent::savePropertiesAsSeparateFiles();
		DebugBarEventsFile::saveIfLocal();
		// messages are saved in is done in TestLogsFile DebugMessagesFile::saveIfLocal();
		//DebugBarSqlFile::saveIfLocal();
	}
}
