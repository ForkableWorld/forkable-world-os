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
class BaseNumberOfUsersWhereReferrerUserProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of Users for this Referrer User.
                    [Formula: update wp_users
                        left join (
                            select count(ID) as total, referrer_user_id
                            from wp_users
                            group by referrer_user_id
                        )
                        as grouped on wp_users.ID = grouped.referrer_user_id
                    set wp_users.number_of_users_where_referrer_user = count(grouped.total)]';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::REFERRER_USER_ID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::REFERRER_USER;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_users_where_referrer_user';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Users Where Referrer User';
	public $type = self::TYPE_INTEGER;

}
