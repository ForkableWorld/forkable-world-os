<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsJsonEncoded;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Studies\StudyImages;
class BaseStudyImagesProperty extends BaseProperty{
    use IsJsonEncoded;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Provided images will override the auto-generated images';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::CREATE_STUDY;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::STUDY;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'study_images';
	public $canBeChangedToNull = true;
	public $phpType = StudyImages::class;
	public $title = 'Study Images';
	public $type = PhpTypes::OBJECT;
	public function getExample(): StudyImages{
        return new StudyImages();
    }
}
