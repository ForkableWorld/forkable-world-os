<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Storage\DB\QMDB;
use App\Traits\PropertyTraits\IsJsonEncoded;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Slim\Model\Measurement\AdditionalMetaData;
class BaseNoteProperty extends BaseProperty{
    use IsJsonEncoded;
	public $dbInput = 'text,65535:nullable';
	public $dbType = QMDB::TYPE_TEXT;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'An optional note the user may include with their measurement';
	public $fieldType = QMDB::TYPE_TEXT;
	public $fontAwesome = FontAwesome::EVERNOTE;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::ACTIVITIES_MUSICAL_NOTE_1;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'note';
	public $phpType = AdditionalMetaData::class;
	public $title = 'Note';
	public $type = AdditionalMetaData::class;
	public function getExample(): AdditionalMetaData{
        return new AdditionalMetaData();
    }
}
