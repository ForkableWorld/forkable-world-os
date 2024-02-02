<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use App\Properties\UserVariableRelationship\CorrelationIdProperty;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\PropertyTraits\IsCalculated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Variables\QMUserVariable;
class BaseBestUserVariableRelationshipIdProperty extends CorrelationIdProperty{
	use ForeignKeyIdTrait;
    use IsCalculated;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The user variable relationship including this variable with the greatest strength and statistical power';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::AGGREGATE_CORRELATION;
	public $htmlType = 'text';
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
    public $isPrimary = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'best_user_variable_relationship_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $title = 'Best User Variable Relationship';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:1|max:2147483647';
    /**
     * @return UserVariableRelationship
     */
    public static function getForeignClass(): string{
        return UserVariableRelationship::class;
    }
    /**
     * @param UserVariable|QMUserVariable $uv
     * @return int
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($uv): ?int{
        $best = $uv->setBestUserVariableRelationship();
        if(!$best){
            $uv->logInfo("No best user variable relationship id");
            return null;
        }
        $uv->setAttribute(static::NAME, $best->id);
        return $best->id;
    }
}
