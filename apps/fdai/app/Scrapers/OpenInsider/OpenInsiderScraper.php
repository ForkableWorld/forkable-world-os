<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\OpenInsider;
use App\Exceptions\NotFoundException;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Scrapers\EconomicScraper;
use App\Scrapers\OpenInsider\Paths\OpenInsiderPath;
use App\Units\IndexUnit;
use App\VariableCategories\EconomicIndicatorsVariableCategory;
class OpenInsiderScraper extends EconomicScraper
{
    protected static $measurements = [];
    public static function valueRatioUserVariable(): UserVariable {
        $me = new static();
        $paths = $me->getPaths();
        return $paths[0]->getUserVariable(OpenInsiderPath::RATIO_OF_VALUE_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES);
    }
    public static function numberOfPurchasesToSalesRatioUserVariable(): UserVariable {
        $me = new static();
        $paths = $me->getPaths();
        return $paths[0]->getUserVariable(OpenInsiderPath::RATIO_OF_NUMBER_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES);
    }
    /**
     * @param string|int $timeAt
     * @return float
     * @throws NotFoundException
     */
    public static function getNumberRatio($timeAt): float {
        $v = static::numberOfPurchasesToSalesRatioUserVariable();
        $m = $v->getDailyMeasurement($timeAt);
        if(!$m){
            throw new NotFoundException("$timeAt not found");
        }
        return $m->value;
    }
    /**
     * @param string|int $timeAt
     * @return float
     * @throws NotFoundException
     */
    public static function getValueRatio($timeAt): float {
        $v = static::valueRatioUserVariable();
        $m = $v->getDailyMeasurement($timeAt);
        if(!$m){
            throw new NotFoundException("$timeAt not found");
        }
        return $m->value;
    }
    public function getBaseUrl(): string{
        return "http://openinsider.com";
    }
    public function getScraperVariableData():array{
        return [
            Variable::FIELD_VARIABLE_CATEGORY_ID => EconomicIndicatorsVariableCategory::ID,
            Variable::FIELD_DEFAULT_UNIT_ID => IndexUnit::ID,
            Variable::FIELD_COMBINATION_OPERATION => BaseCombinationOperationProperty::COMBINATION_MEAN,
            Variable::FIELD_MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS => 86400,
            Variable::FIELD_OUTCOME => EconomicIndicatorsVariableCategory::OUTCOME,
        ];
    }
    public function getResponseType(): string{
        return self::RESPONSE_TYPE_CSV;
    }
    public function getPathsClasses(): array{
        return [
            OpenInsiderPath::class,
        ];
    }
	public function getBaseApiUrl(): string{
		// TODO: Implement getBaseApiUrl() method.
	}
	/**
	 * @param string $url
	 * @param $body
	 * @param array $headers
	 * @param string $method
	 * @param int $code
	 * @return void
	 */
	public function saveConnectorRequestResponse(string $url, $body, array $headers = [], string $method = 'GET',
	                                             int    $code = 200): void{
		// TODO: Implement saveConnectorRequestResponse() method.
	}
}
