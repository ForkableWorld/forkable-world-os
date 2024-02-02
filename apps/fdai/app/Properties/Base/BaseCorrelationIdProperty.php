<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\UserVariableRelationship;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCorrelationIdProperty extends BaseProperty{
	use ForeignKeyIdTrait;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'correlation_id';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::AGGREGATE_CORRELATION;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'correlation_id';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'User Variable Relationship ID';
	public $type = self::TYPE_INTEGER;
    public static function getForeignClass(): string{
        return UserVariableRelationship::class;
    }
}
