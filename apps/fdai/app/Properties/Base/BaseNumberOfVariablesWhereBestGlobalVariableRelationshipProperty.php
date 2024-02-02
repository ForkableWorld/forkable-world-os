<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfVariablesWhereBestGlobalVariableRelationshipProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of Variables for this Best Global Variable Relationship.
                    [Formula: update global_variable_relationships
                        left join (
                            select count(id) as total, best_global_variable_relationship_id
                            from variables
                            group by best_global_variable_relationship_id
                        )
                        as grouped on global_variable_relationships.id = grouped.best_global_variable_relationship_id
                    set global_variable_relationships.number_of_variables_where_best_global_variable_relationship = count(grouped.total)]';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::AGGREGATE_CORRELATION;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::AGGREGATE_CORRELATION;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_variables_where_best_global_variable_relationship';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Variables Where Best Global Variable Relationship';
	public $type = self::TYPE_INTEGER;

}
