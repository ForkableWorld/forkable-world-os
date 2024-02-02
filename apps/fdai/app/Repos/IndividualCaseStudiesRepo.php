<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Repos;
use App\Computers\ThisComputer;
class IndividualCaseStudiesRepo extends GitRepo {
	public const PUBLIC = true;
	public static $REPO_NAME = 'qm-individual-case-studies';
	public static function build(){
		ThisComputer::exec("bundle install");
	}
}
