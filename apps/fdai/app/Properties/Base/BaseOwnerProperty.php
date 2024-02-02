<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseOwnerProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsArray;
	public $dbInput = 'text';
	public $dbType = 'text';
	public $default = 'undefined';
	public $description = 'Example: {login:mikepsinn,id:2808553,node_id:MDQ6VXNlcjI4MDg1NTM=,avatar_url:https://avatars.githubusercontent.com/u/2808553?v=4,gravatar_id:,url:https://api.github.com/users/mikepsinn,html_url:https://github.com/mikepsinn,followers_url:https://api.github.com/users/mikepsinn/followers,following_url:https://api.github.com/users/mikepsinn/following{/other_user},gists_url:https://api.github.com/users/mikepsinn/gists{/gist_id},starred_url:https://api.github.com/users/mikepsinn/starred{/owner}{/repo},subscriptions_url:https://api.github.com/users/mikepsinn/subscriptions,organizations_url:https://api.github.com/users/mikepsinn/orgs,repos_url:https://api.github.com/users/mikepsinn/repos,events_url:https://api.github.com/users/mikepsinn/events{/privacy},received_events_url:https://api.github.com/users/mikepsinn/received_events,type:User,site_admin:false}';
	public $example = 'Array';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'owner';
	public $order = 99;
	public $phpType = 'string';
	public $rules = 'required';
	public $showOnDetail = true;
	public $title = 'Owner';
	public $type = 'string';
	public $validations = 'required';

}
