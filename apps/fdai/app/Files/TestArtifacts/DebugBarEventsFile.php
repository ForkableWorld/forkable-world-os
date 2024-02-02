<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use App\Files\FileExtension;
use App\Types\QMStr;
use Tests\QMDebugBar;
class DebugBarEventsFile extends AbstractDebugbarFile {
	public static function getDefaultExtension(): string{ return FileExtension::TXT; }
	public static function getData(): string{
		$events = QMDebugBar::getEvents();
		if(!$events){
			return "No events from QMDebugBar::getEvents";
		}
		$str = '';
		$clean = [];
		foreach($events as $i => $event){
			$event = str_replace('App\\Models\\', '', $event);
			$event = QMStr::after("\\Events\\", $event, $event);
			$clean[] = $event;
		}
		return implode("\n", $clean);
	}
}
