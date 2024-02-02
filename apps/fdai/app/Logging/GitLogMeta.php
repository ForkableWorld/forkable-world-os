<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Logging;
use App\Types\QMArr;
use App\Utils\Env;
class GitLogMeta {
	public static function get():array{
		$arr = [
			'CHANGE_URL' => Env::getFormatted('CHANGE_URL'),
			'GIT_COMMIT' => Env::getFormatted('GIT_COMMIT_HASH') ?? Env::getFormatted('GIT_COMMIT'),
			'GIT_URL' => Env::getFormatted('GIT_URL'),
		];
		return QMArr::notEmptyValues($arr);
	}
}
