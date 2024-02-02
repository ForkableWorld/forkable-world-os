<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsHtml;
use App\Traits\HasFilter;
use App\Types\PhpTypes;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use Illuminate\Database\Eloquent\Builder;
use App\Fields\Field;
use App\Slim\Middleware\QMAuth;
use OpenApi\Generator;
class BaseInternalErrorMessageProperty extends BaseProperty{
	use IsHtml;
    use HasFilter;
    const OPTION_has_errors = 'has_errors';
    const OPTION_no_errors = 'no_errors';
    public $dbInput = 'string,255:nullable';
	public $dbType = 'text';
	public $default = Generator::UNDEFINED;
	public $description = 'internal_error_message';
	public $example = 'https://local.quantimo.do/admin/ignitionReport?time=071515';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::FAILED;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 1000;
	public $name = self::NAME;
	public const NAME = 'internal_error_message';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:1000';
	public $title = 'Internal Error';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:1000';
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getHtmlField(function($value, $resource, $attribute){
            if(stripos($value, "https") === 0){
                return HtmlHelper::generateLink("Error Details", $value, true);
            }
            return $value;
        }, $name, $resolveCallback);
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $value = $this->getDBValue();
        if($value && stripos($value, "non-object") !== false &&
	        stripos($value, 'line') === false){
            le("Why doesn't FIELD_INTERNAL_ERROR_MESSAGE contain line number for $value");
        }
        if($value === false){
            $this->throwException("should not equal false");
        }
        if($value === "0"){
            $this->throwException("should not equal 0");
        }
    }
    /**
     * Apply the filter to the given query.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilter($query, $type): Builder{
        if($type === self::OPTION_has_errors){
            $query->whereNotNull(static::getTable().'.'.static::NAME);
        }
        if($type === self::OPTION_no_errors){
            $query->whereNull(static::getTable().'.'.static::NAME);
        }
        return $query;
    }
    public function getFilterOptions(): array{
        return [
            'Has Errors' => self::OPTION_has_errors,
            'No Errors' => self::OPTION_no_errors,
        ];
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return QMAuth::isAdmin();}
    public function shouldShowFilter(): bool{return QMAuth::isAdmin();}
}
