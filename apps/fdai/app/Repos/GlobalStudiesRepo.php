<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Computers\ThisComputer;
class GlobalStudiesRepo extends GitRepo {
	public const PUBLIC = true;
	public static $REPO_NAME = 'qm-global-studies';
	public static function build(){
		ThisComputer::exec("bundle install");
	}
}
