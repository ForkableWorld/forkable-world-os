<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use Exception;
use App\Variables\QMVariable;
class BaseMostCommonSourceNameProperty extends BaseNameProperty{
	use IsString;
    use IsCalculated;
    const MIN_LENGTH = 2; // up only has 2 letters
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The most common data source for this variable';
	public $example = 'TigerView';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::SOURCE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::SOURCE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 255;
    public $minLength = self::MIN_LENGTH;
	public $name = self::NAME;
	public const NAME = 'most_common_source_name';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $title = 'Most Common Source Name';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:255';
    /**
     * @param QMVariable $model
     * @return mixed
     */
    public static function calculate($model){
        $nonUniqueDataSourceNames = $model->calculateNonUniqueDataSourceNames();
        if(isset($nonUniqueDataSourceNames[0])){
            try {
                $c = array_count_values($nonUniqueDataSourceNames);
                $mostCommonSourceName = array_search(max($c), $c, true);
            } catch (Exception $e) {
                $model->logError("array_count_values won't work for this sourceNames: ".
                    json_encode($nonUniqueDataSourceNames));
            }
        }
        $mostCommonSourceName = $mostCommonSourceName ?? null;
        $model->setAttribute(static::NAME, $mostCommonSourceName);
        return $mostCommonSourceName;
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return false;}
}
