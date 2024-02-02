<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors\Responses\Github;
use App\DataSources\Connectors\Responses\BaseResponseObject;
class BaseLicense extends BaseResponseObject
{
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseLicense.key
     */
    public $key;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseLicense.name
     */
    public $name;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseLicense.spdx_id
     */
    public $spdx_id;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseLicense.url
     */
    public $url;
    /**
     * @var string
     * @link https://api.highcharts.com/highcharts/baseLicense.node_id
     */
    public $node_id;
}
