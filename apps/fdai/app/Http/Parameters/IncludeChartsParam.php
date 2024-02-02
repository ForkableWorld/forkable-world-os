<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Parameters;
use App\Slim\View\Request\QMRequest;
class IncludeChartsParam extends BaseParam {
	public static function getSynonyms(): array{
		return [QMRequest::PARAM_INCLUDE_CHARTS];
	}
	/**
	 * @return array|mixed|null
	 */
	public static function includeCharts(): bool{
		return QMRequest::getBool(QMRequest::PARAM_INCLUDE_CHARTS);
	}
}
