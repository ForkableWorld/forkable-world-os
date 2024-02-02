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
class BaseNumberOfOauthClientsProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of OAuth Clients for this User.
                [Formula:
                    update wp_users
                        left join (
                            select count(client_id) as total, user_id
                            from oa_clients
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_oauth_clients = count(grouped.total)
                ]
                ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CLIENT_ID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CLIENT_ID;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_oauth_clients';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Oauth Clients';
	public $type = self::TYPE_INTEGER;

}
