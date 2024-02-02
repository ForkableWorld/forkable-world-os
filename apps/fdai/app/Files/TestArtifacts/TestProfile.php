<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use App\Files\TextFile;
use App\Utils\QMProfile;
class TestProfile extends TextFile {
	use IsTestArtifactFile;
	public static function getData(): string{
		$p = QMProfile::endProfile();
		return $p;
	}
}
