<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons;
use App\Fields\HasMany as HasManyAlias;
use App\Models\BaseModel;
use App\Types\QMStr;
use Illuminate\Database\Eloquent\Relations\HasMany;
class HasManyRelationshipButton extends RelationshipButton
{
    protected $foreignKey; //Variable::FIELD_BEST_AGGREGATE_CORRELATION_ID
    /**
     * HasManyRelationshipButton constructor.
     * @param string|BaseModel|null $methodOrModel
     * @param HasMany|null $relation
     */
    public function __construct($methodOrModel = null, HasMany $relation = null){
        if(!$methodOrModel){return;}
        parent::__construct($methodOrModel,$relation);
        $parent = $this->getParent();
        $relation = $this->getButtonRelation();
        $this->foreignKey = $relation->getQualifiedForeignKeyName();
        if($parent->hasId()){
            $this->generateUrl();
            $this->generateBadge();
        }
        $this->getTooltip();
    }
    public function getTooltip(): ?string {
        $tooltip = $this->tooltip;
        $parent = $this->getParent();
        $relatedModel = $this->getRelated();
        $parentTitle = $this->getParentTitle();
        $buttonTitle = $this->getTitleAttribute();
        if($tooltip){
            if($parent->hasId()){
                $classTitle = $parent->getClassNameTitle();
                $tooltip = str_ireplace("this $classTitle", $parent->getTitleAttribute(), $tooltip);
                $this->setTooltip($tooltip);
                return $tooltip;
            }
        }
        if(strpos($buttonTitle, " Where ")){
            $tooltip = str_replace(" Where ", " where this is the ", $buttonTitle);
        } else{
            $tooltip = "$buttonTitle for this $parentTitle.  ";
            $tooltip = $relatedModel->getSubtitleAttribute();
        }
        $this->setTooltip($tooltip); // Don't use "View " or it won't replace with constants when generating buttons
        return $tooltip;
    }
    /**
     * @return array
     */
    public function getForeignKeyParams(): array{
        $parent = $this->getParent();
        $foreignKeys = $this->foreignKey;
        $params = [];
        if(!is_array($foreignKeys)){
            $foreignKeys = [$foreignKeys];
        }
        if(is_array($foreignKeys)){
            foreach($foreignKeys as $key){
                $withoutTable = QMStr::after('.', $key, $key);
                if(count($foreignKeys) === 1){
                    $params[$withoutTable] = $parent->getId();
                } else {
                    $params[$withoutTable] = $parent->getAttribute($withoutTable);
                }
            }
        }
        return $params;
    }
    protected function generateBadge(): void{
        $parent = $this->getParent();
        $numberMethod = HasManyAlias::$number_of_ .$this->methodName;
        if($parent->hasColumn($numberMethod)){
            $number = $parent->getAttribute($numberMethod) ?? "N/A";
            $this->setBadgeText($number);
        }
    }
    protected function generateUrl(): void{
        $relatedClass = $this->getRelatedClass();
        $params = $this->getForeignKeyParams();
        $url = $relatedClass::getDataLabIndexUrl($params);
        $this->setUrl($url);
    }
}
