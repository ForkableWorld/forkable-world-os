<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\FederalReserve;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Scrapers\EconomicScraper;
use App\Scrapers\FederalReserve\Paths\TradeWeightedUSDollarIndexPath;
use App\Units\DollarsUnit;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
/** Class FederalReserveScraper
 * @package App\Scrapers\FederalReserve
 * https://fred.stlouisfed.org/docs/api/fred/series_observations.html
 * https://fred.stlouisfed.org/series/AAA10Y
 * https://fred.stlouisfed.org/series/VIXCLS
 * https://fred.stlouisfed.org/series/USEPUINDXD
 * https://fred.stlouisfed.org/series/DTWEXAFEGS
 */
class FederalReserveScraper extends EconomicScraper {
    /**
     * @return string
     */
    public function getBaseApiUrl(): string{
        return "https://api.stlouisfed.org/fred";
    }
    /**
     * @return array
     */
    public function getPathsClasses(): array{
        return [
            ObservationsPath::class,
        ];
    }
    /**
     * @return array
     */
    public function getScraperVariableData():array{
        return [
            Variable::FIELD_VARIABLE_CATEGORY_ID => EconomicIndicatorsVariableCategory::ID,
            Variable::FIELD_DEFAULT_UNIT_ID => DollarsUnit::ID,
            Variable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_MEAN,
            Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 60,
        ];
    }
    /**
     * @return string
     */
    public function getResponseType(): string{
        return self::RESPONSE_TYPE_JSON;
    }
    protected function getSeriesVariableData():array{
        return [
            "AAA10Y" => [],
            "CSUSHPISA" => [],
            "DTWEXAFEGS" => [],
            "USEPUINDXD" => [
                Variable::FIELD_DESCRIPTION => TradeWeightedUSDollarIndexPath::DESCRIPTION,
            ],
            "VIXCLS" => [],
        ];
    }
	/**
	 * @param string $url
	 * @param $body
	 * @param array $headers
	 * @param string $method
	 * @param int $code
	 */
	public function saveConnectorRequestResponse(string $url, $body, array $headers = [], string $method = 'GET',
		int $code = 200): void{
		// TODO: Implement saveConnectorRequestResponse() method.
	}
}
