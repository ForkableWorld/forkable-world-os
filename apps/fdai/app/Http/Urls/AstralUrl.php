<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Urls;
class AstralUrl extends AbstractUrl {
	public static function getAstralUrl(string $path, array $params = []): string{
		return qm_url("astral/resources/$path", $params);
	}
	protected function generatePath(): string{ return "/astral"; }
}
