<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors\Responses\Github;
use App\DataSources\Connectors\Responses\BaseResponseObject;
class BasePermissions extends BaseResponseObject
{
    /**
     * @var bool
     * @link https://api.highcharts.com/highcharts/basePermissions.admin
     */
    public $admin;
    /**
     * @var bool
     * @link https://api.highcharts.com/highcharts/basePermissions.push
     */
    public $push;
    /**
     * @var bool
     * @link https://api.highcharts.com/highcharts/basePermissions.pull
     */
    public $pull;
}
