<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfCommonTagsWhereTaggedVariableProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of Common Tags for this Tagged Variable.
                [Formula:
                    update variables
                        left join (
                            select count(id) as total, tagged_variable_id
                            from common_tags
                            group by tagged_variable_id
                        )
                        as grouped on variables.id = grouped.tagged_variable_id
                    set variables.number_of_common_tags_where_tagged_variable = count(grouped.total)
                ]
                ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::PRIMARY_OUTCOME_VARIABLE_ID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::PRIMARY_OUTCOME_VARIABLE;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_common_tags_where_tagged_variable';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Common Tags Where Tagged Variable';
	public $type = self::TYPE_INTEGER;
}
