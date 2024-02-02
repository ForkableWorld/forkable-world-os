<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Logging\QMLog;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\Properties\BaseProperty;
use App\Fields\Field;
use Mdixon18\Fontawesome\Fontawesome;
use OpenApi\Generator;
class BaseFontAwesomeProperty extends BaseProperty{
	use IsString;
    const FONT_AWESOME = 'Font Awesome';
    public $dbInput = 'string,100:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'font_awesome';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = \App\UI\FontAwesome::FONT_AWESOME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DESIGN_TOOL_COLLECTION_FONT;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'font_awesome';
    public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $showOnDetail = true;
	public $title = 'Font Awesome';
	public $type = PhpTypes::STRING;
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
    public function getUpdateField($resolveCallback = null, string $name = null): Field{
        return $this->getFontAwesomeField($name, $resolveCallback);
    }
    public function getCreateField($resolveCallback = null, string $name = null): Field{
        return $this->getFontAwesomeField($name, $resolveCallback);
    }
    public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return $this->getFontAwesomeField($name, $resolveCallback);
    }
    public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getFontAwesomeField($name, $resolveCallback);
    }
    public function getHardCodedValue(){
        $val = $this->getDBValue();
        if(!$val){return $val;}
        $const = \App\UI\FontAwesome::findConstantNameWithValue($val);
        return \App\UI\FontAwesome::class."::".$const;
    }
    /**
     * @param string|null $name
     * @param $resolveCallback
     * @return Fontawesome
     */
    protected function getFontAwesomeField(?string $name, $resolveCallback): Fontawesome{
        $val = $this->getDBValue();
        if(!$val){
            $parent = $this->getParentModel();
            if($parent->hasId()){
                QMLog::error("Could not get FontAwesome for ".$this->getParentModel()->getNameAttribute());
            }
            return Fontawesome::make(self::FONT_AWESOME);
        }
        $arr = explode(" ", $val);
        if(!isset($arr[1])){
            QMLog::error("Could not explode Fontawesome: $val for ".$this->getParentModel()->getNameAttribute());
            return Fontawesome::make(self::FONT_AWESOME);
        }
        return Fontawesome::make(self::FONT_AWESOME)->defaultIcon($arr[0], $arr[1]);
    }
}
