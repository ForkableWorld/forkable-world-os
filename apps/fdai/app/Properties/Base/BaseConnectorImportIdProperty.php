<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\ConnectorImport;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseConnectorImportIdProperty extends BaseIntegerIdProperty{
	use ForeignKeyIdTrait;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The ID of the data import job for that connector';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CONNECTOR;
	public $htmlType = 'text';
	public $image = ImageUrls::CONNECTOR;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'connector_import_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $title = 'Connector Import';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|integer|min:1|max:2147483647';
    /**
     * @return ConnectorImport
     */
    public static function getForeignClass(): string{
        return ConnectorImport::class;
    }
}
