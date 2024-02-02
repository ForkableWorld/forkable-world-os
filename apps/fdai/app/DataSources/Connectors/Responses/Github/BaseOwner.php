<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors\Responses\Github;
use App\DataSources\Connectors\Responses\BaseResponseObject;
class BaseOwner extends BaseResponseObject
{
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.login
     */
    public $login;
    /**
     * @var int
     * @link https://api.highcharts.com/highcharts/baseOwner.id
     */
    public $id;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.node_id
     */
    public $node_id;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.avatar_url
     */
    public $avatar_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.gravatar_id
     */
    public $gravatar_id;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.url
     */
    public $url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.html_url
     */
    public $html_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.followers_url
     */
    public $followers_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.following_url
     */
    public $following_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.gists_url
     */
    public $gists_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.starred_url
     */
    public $starred_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.subscriptions_url
     */
    public $subscriptions_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.organizations_url
     */
    public $organizations_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.repos_url
     */
    public $repos_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.events_url
     */
    public $events_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.received_events_url
     */
    public $received_events_url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseOwner.type
     */
    public $type;
    /**
     * @var bool
     * @link https://api.highcharts.com/highcharts/baseOwner.site_admin
     */
    public $site_admin;
}
