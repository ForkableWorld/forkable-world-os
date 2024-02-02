<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Computers\ThisComputer;
use App\Types\QMArr;
use App\Utils\Env;
class BuildLogMeta {
	public static function get():array{
		$arr = [
			'BUILD_LOG' => ThisComputer::getBuildConsoleUrl(),
			'HOST' => ThisComputer::getHostAddress(),
			'CHANGE_URL' => Env::getFormatted('CHANGE_URL'),
			'GIT_COMMIT' => Env::getFormatted('GIT_COMMIT_HASH') ?? Env::getFormatted('GIT_COMMIT'),
			'GIT_URL' => Env::getFormatted('GIT_URL'),
			'HOME' => Env::getFormatted('HOME'),
			'JOB_NAME' => Env::getFormatted('JOB_NAME'),
			'NODE_NAME' => Env::getFormatted('NODE_NAME'),
			'PHP_IDE_CONFIG' => Env::getFormatted('PHP_IDE_CONFIG'),
			'PWD' => Env::getFormatted('PWD'),
			'QM_API_FOLDER' => Env::getFormatted('QM_API'),
			'QM_API_SHARED' => Env::getFormatted('QM_API_SHARED'),
			'RUN_ARTIFACTS_DISPLAY_URL' => Env::getFormatted('RUN_ARTIFACTS_DISPLAY_URL'),
			'RUN_DISPLAY_URL' => Env::getFormatted('RUN_DISPLAY_URL'),
			'SSH_CLIENT' => Env::getFormatted('SSH_CLIENT'),
			'TEST_PATH' => Env::getFormatted('TEST_PATH'),
			'USER' => Env::getFormatted('USER'),
			'WORKSPACE' => Env::getFormatted('WORKSPACE'),
		];
		return QMArr::notEmptyValues($arr);
	}
}
