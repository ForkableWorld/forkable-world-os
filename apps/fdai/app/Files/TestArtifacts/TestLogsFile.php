<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use App\Files\TextFile;
use App\Storage\TestMemory;
use App\Types\QMStr;
class TestLogsFile extends TextFile {
	use IsTestArtifactFile;
	protected static $foundTestName = false; // Skip all setup frames before the test name appears
	public static function getData(): ?string{
		$logMessages = TestMemory::get(TestMemory::LOGS) ?? [];
		$str = "";
		foreach($logMessages as $message){
			$str .= $message->getName() . "\n";
		}
		$str = QMStr::stripDates($str);
		$str = QMStr::stripRepoPaths($str);
		return $str;
	}
	public static function getLines(): array{
		$logMessages = TestMemory::get(TestMemory::LOGS);
		$arr = [];
		foreach($logMessages as $message){
			$arr = $message->getName();
		}
		return $arr;
	}
}
