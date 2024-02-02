<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsHtml;
use App\Models\WpPost;
use App\Storage\DB\QMDB;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BasePostContentProperty extends BaseProperty{
	use IsHtml;
	protected $isHtml = true;
	protected $isPublic = true;
	public $dbInput = 'text:nullable';
	public $dbType = QMDB::TYPE_MEDIUMTEXT;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Holds all the content for the post, including HTML, shortcodes and other content.';
    public $fieldType = 'text';
	public $fontAwesome = FontAwesome::POST;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::POST;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 6000 * 1024; // TODO: Reduce this to 500 * 1024; // KB
	public $minLength = WpPost::MINIMUM_POST_CONTENT_LENGTH;
	public $name = self::NAME;
	public const NAME = 'post_content';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|string|min:280|max:2147483647';
	public $title = 'Post Content';
	public $type = PhpTypes::STRING;
	public $validations = 'required|string|min:280.|max:2147483647';
	protected $requiredStrings = [
        "<!-- wp:html -->",
        "<!-- /wp:html -->"
    ];
    protected $shouldNotContain = [
        //"bootstrap-combined.no-icons.min.css", // TODO: remove because it messes up lists and not sure why we need it
        "Too slow",
        //"vlp-link",
        "https://play.google.com/store/apps/details?id=com.quantimodo.quantimodo", // Don't have time to maintain and should be in the footer not post_content
        "https://itunes.apple.com/us/app/quantimodo-life-tracker/id1115037060?mt=8", // Don't have time to maintain and should be in the footer not post_content
        "How is your Overall Mood today?",
        "Studies from Your Data",
        "t found any relationships",
        "Principal Investigator System",
        "There are not enough ",
        '<input type="text" class="form-control',
        'ionic/Modo/www',
        " 0 raw measurements from ",
        "Population for Population",
        "pimunsi6t5ysd81k.quantimo.do", // Not sure why we're linking to this weird site
        "style=\"width: 100%; max-width: 681px;", // This is from the old style of header image.  We now use the one with class="external-img wp-post-image "
        //"&amp;effectVariableId=",
        "/public/img/", // image file paths should have been replaced by urls!
        "<!-- wp:html -->
publish
<!-- /wp:html -->"
        // We need this for sharing buttons so it's not repeated for ever button "<style>", // Messes up other wordpress theme elements and takes almost as much disk space as inline anyway
    ];
}
