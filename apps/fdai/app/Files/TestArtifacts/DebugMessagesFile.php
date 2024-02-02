<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\TestArtifacts;
use Tests\QMDebugBar;
class DebugMessagesFile extends AbstractDebugbarFile {
	public static function getData(): array{ return QMDebugBar::getMessages(); }
}
