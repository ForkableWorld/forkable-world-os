<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
class BaseMostCommonConnectorIdProperty extends BaseConnectorIdProperty{
	public $description = 'most_common_connector_id';
	public $name = self::NAME;
	public const NAME = 'most_common_connector_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Most Common Connector';
	public $canBeChangedToNull = true;
}
