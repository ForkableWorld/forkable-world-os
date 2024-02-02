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
class BaseNumberOfSentEmailsProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of Sent Emails for this User.
                    [Formula: update wp_users
                        left join (
                            select count(id) as total, user_id
                            from sent_emails
                            group by user_id
                        )
                        as grouped on wp_users.ID = grouped.user_id
                    set wp_users.number_of_sent_emails = count(grouped.total)]';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::LAST_EMAIL;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::EMAIL;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_sent_emails';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Sent Emails';
	public $type = self::TYPE_INTEGER;

}
