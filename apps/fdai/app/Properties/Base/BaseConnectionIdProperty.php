<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\DataSources\QMConnector;
use App\Models\Connection;
use App\Traits\ForeignKeyIdTrait;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Doctrine\DBAL\Types\Types;
use OpenApi\Generator;
class BaseConnectionIdProperty extends BaseIntegerIdProperty {
	use ForeignKeyIdTrait;
	public const NAME = 'connection_id';
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false,true';
	public $dbType = Types::INTEGER;
	public $default = Generator::UNDEFINED;
	public $description = 'The ID of the data source connection for the data owner';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CONNECTION;
	public $htmlType = 'text';
	public $image = ImageUrls::CONNECTION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isPrimary = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $title = 'Connection';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|integer|min:1|max:2147483647';
	/**
	 * @return Connection
	 */
	public static function getForeignClass(): string{
		return Connection::class;
	}
	public function getHardCodedValue(): ?string{
		$val = $this->getDBValue();
		if(!$val){
			return null;
		}
		$unit = QMConnector::find($val);
		return get_class($unit) . "::ID";
	}
}
