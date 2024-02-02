<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Parameters;
use App\Slim\View\Request\QMRequest;
class IncludeTagsParam extends BaseParam {
	public static function getSynonyms(): array{
		return ['includeTags'];
	}
	/**
	 * @return bool
	 */
	public static function includeTags(): bool{
		return QMRequest::getBool(QMRequest::PARAM_INCLUDE_TAGS);
	}
}
