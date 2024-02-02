<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\VariableRelationships\QMVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Properties\BaseProperty;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\Stats;
use App\Fields\Field;
use OpenApi\Generator;
class BaseEffectFollowUpPercentChangeFromBaselineProperty extends BaseProperty{
	use IsFloat;
    const DOWN_ARROW = "&darr; ";
    const UP_ARROW = "&uarr; ";
    public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = Generator::UNDEFINED;
	public $description = 'Outcome Average at Follow-Up (The average value seen for the outcome during the duration of action following the onset delay of the treatment)';
	public $example = 3.7;
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::PERCENT_SOLID;
	public $htmlType = 'text';
	public $image = ImageUrls::ESSENTIAL_COLLECTION_PERCENT_1;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	//public $maximum = 10000;  // Should we have a min and max for this?
	//public $minimum = -10000;  // Should we have a min and max for this?
    public $canBeChangedToNull = false;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'effect_follow_up_percent_change_from_baseline';
	public $phpType = 'float';
	public $rules = 'required|numeric';
	public $title = 'Change From Baseline';
	public $type = self::TYPE_NUMBER;
	public $validations = 'required|numeric';
    /**
     * @param bool $arrows
     * @param float $percentChange
     * @return string
     */
    public static function percentToHigherLowerString(bool $arrows, float $percentChange): string{
        if($percentChange < 0){
            $percentChange *= -1;
            if($arrows){
                $changeString = self::DOWN_ARROW.$percentChange."%";
            }else{
                $changeString = $percentChange."% lower";
            }
        }else if($arrows){
            $changeString = self::UP_ARROW.$percentChange."%";
        }else{
            $changeString = $percentChange."% higher";
        }
        return $changeString;
    }
    /**
     * @param bool $arrows
     * @param float $change
     * @return string
     */
    public static function percentToIncreaseDecreaseString(bool $arrows, float $change): string{
        $change = Stats::roundByNumberOfSignificantDigits($change, QMVariableRelationship::SIG_FIGS);
        if ($arrows) {
            $changeString = "&uarr; " . $change . "%";
        } else {
            $changeString = $change . "% increase";
        }
        if ($change < 0) {
            $change *= -1;
            if ($arrows) {
                $changeString = "&darr; " . $change . "%";
            } else {
                $changeString = $change . "% decrease";
            }
        }
        return $changeString;
    }
    /**
     * @param string $cause
     * @param string $effect
     * @param string $val
     * @return string
     */
    public static function generateSentence(string $cause, string $effect, $val): string{
        if($val === null){
            return "The effect of $cause on $effect has not yet been determined. ";
        }
        $val = (int)$val;
		if(empty($cause)){le('empty($cause)');}
        if($val < 0){
            return $effect." was ".self::generateFragment($cause, $val).". ";
        }
        return $effect." was ".self::generateFragment($cause, $val).". ";
    }
    /**
     * @param string $causeName
     * @param float $val
     * @return string
     */
    public static function generateFragment(string $causeName, float $val): string{
        if(empty($causeName)){le("No cause");}
        return self::generateString($val)." following above average $causeName";
    }
    /**
     * @param string $causeName
     * @param float $change
     * @param string $effectValence
     * @return string
     */
    public static function generateFragmentHtml(string $causeName, float $change, string $effectValence): string{
        if($change < 0){
            return self::generateIndexHtml($change, $effectValence)." following above average $causeName";
        }
        return self::generateIndexHtml($change, $effectValence)." following above average $causeName";
    }
    /**
     * @param string $causeName
     * @param float $change
     * @param string $effectValence
     * @param string $url
     * @return string
     */
    public static function generateFragmentLink(string $causeName, float $change, string $effectValence, string $url): string{
        $str = self::generateFragmentHtml($causeName, $change, $effectValence);
        return HtmlHelper::generateLink($str, $url, true, "Click to see full study on this relationship");
    }
    public static function generateString(float $change, int $sigFigs = 2): string{
        if(round($change) === 0){
            return "Unchanged";
        }
        $rounded = Stats::roundByNumberOfSignificantDigits($change, $sigFigs);
        if($rounded > 0){
            return "↑".$rounded."% Higher";
        }else{
            return "↓".abs($rounded)."% Lower";
        }
    }
    /**
     * @param $change
     * @param string $effectValence
     * @return string
     */
    public static function generateColor($change, string $effectValence): string{
        if($change === 0){
            return QMColor::HEX_GOOGLE_YELLOW;
        }
        if($effectValence === BaseValenceProperty::VALENCE_NEGATIVE){
            if($change > 0){
                return QMColor::HEX_GOOGLE_PLUS_RED;
            }
            return QMColor::HEX_GOOGLE_GREEN;
        }else{
            if($change > 0){
                return QMColor::HEX_GOOGLE_GREEN;
            }
            return QMColor::HEX_GOOGLE_PLUS_RED;
        }
    }
    /**
     * @param $change
     * @param string $effectValence
     * @return string
     */
    public static function generateIndexHtml($change, string $effectValence): string{
        if($change === null){
            return "Unknown";
        }
        $rounded = round($change);
        $color = BaseEffectFollowUpPercentChangeFromBaselineProperty::generateColor($rounded, $effectValence);
        if($rounded === 0){
            $str = "Unchanged";
        }elseif($rounded > 0){
            $str = "+".$change."% Higher";
        }else{
            $str = "$change% Lower";
        }
        return "<span style='color: $color'>$str</span>";
    }
    public static function generateHyperlink(string $cause,
                                             string $effect,
                                             $val,
                                             string $url,
                                             string $effectValence): string{
        $val = (int)$val;
        $sentence = self::generateSentence($cause, $effect, $val);
        $html = self::generateIndexHtml($val, $effectValence);
        return '<a title="'.$sentence.'"
                        href="'.$url.'"
                        class="no-underline"
                        style="cursor: pointer;">
                        '.$html.'
                </a>';
    }
    public function getIndexField($resolveCallback = null, string $name = null): Field{
	    return $this->getHtmlField(function($value, $resource, $attribute){
	        if($value === null){return "value is null";}
                /** @var UserVariableRelationship $resource */
                return $resource->getEffectSizeLinkToStudyWithExplanation();
            }, $name, $resolveCallback)
            ->sortable(true)
            ->hideWhenCreating();
    }
    public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return $this->getHtmlField(function($value, $resource, $attribute){
            /** @var UserVariableRelationship $resource */
            return $resource->aboveAverageSentence();
        }, $name, $resolveCallback);
    }
    public function getIndexHtml(): string {
	    $c = $this->getParentModel();
	    return self::generateHyperlink($c->getCauseVariableName(),
            $c->getEffectVariableName(),
            round($c->getChangeFromBaseline()),
            $c->getUrl(),
            $c->getEffectVariableValence());
    }
    public function getDetailHtml(): string {
        $c = $this->getParentModel();
        return self::generateSentence($c->getCauseVariableName(),
            $c->getEffectVariableName(),
            $c->getChangeFromBaseline());
    }
    /**
     * @return UserVariableRelationship|GlobalVariableRelationship
     */
    public function getParentModel(): BaseModel{
        return parent::getParentModel();
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
