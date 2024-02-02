<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Parameters;
class IntParameter extends QMParameter
{
    public $example = 10;
    public $in = 'query';
    public $type = "integer";
    public $schema = ["type"=> "integer", "format"=> "int64"];
}
