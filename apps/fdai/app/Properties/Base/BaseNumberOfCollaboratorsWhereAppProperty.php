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
class BaseNumberOfCollaboratorsWhereAppProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of Collaborators for this App.
                [Formula:
                    update applications
                        left join (
                            select count(id) as total, app_id
                            from collaborators
                            group by app_id
                        )
                        as grouped on applications.id = grouped.app_id
                    set applications.number_of_collaborators_where_app = count(grouped.total)
                ]
                ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::COLLABORATORS;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::COLLABORATORS;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_collaborators_where_app';
	public $canBeChangedToNull = true;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Collaborators Where App';
	public $type = self::TYPE_INTEGER;

}
