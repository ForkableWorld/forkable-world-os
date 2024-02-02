<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\ModelValidationException;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\IsDateTime;
use App\Models\WpPost;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use LogicException;
class BasePostDateProperty extends BaseProperty{
	use IsDateTime;
	public $dbInput = self::TYPE_DATETIME;
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Time and date of creation.';
	public $example = '2018-08-31 19:36:07';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::POST;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'post_date';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|date|after_or_equal:2000-01-01';
	public $title = 'Post Date';
	public $type = self::TYPE_DATETIME;
	public $validations = 'required';
	public static function fixInvalidRecords(){
        $posts = WpPost::wherePostDate("0000-00-00 00:00:00")->get()
            ->concat(WpPost::wherePostDate(null)->get());
        foreach ($posts as $post) {
            $post->logInfo($post->post_title);
            $post->post_date = $post->post_date_gmt = now_at();
            try {
                $post->save();
            } catch (ModelValidationException $e) {
                le($e);
            }
        }
    }

}
