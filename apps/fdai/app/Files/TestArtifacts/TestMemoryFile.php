<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use App\Files\TextFile;
use App\Storage\TestMemory;
use App\Utils\EnvOverride;
class TestMemoryFile extends TextFile {
	use IsTestArtifactFile;
	/**
	 * Over-ridden parent because 1 file is too big
	 */
	public static function saveIfLocal(): void{
		if(!EnvOverride::isLocal()){
			return;
		}
		self::savePropertiesAsSeparateFiles();
	}
	public static function getData(): array{ return TestMemory::all(); }
}
