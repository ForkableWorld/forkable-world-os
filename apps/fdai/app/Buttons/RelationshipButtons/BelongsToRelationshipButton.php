<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons;
use App\Exceptions\NoIdException;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
class BelongsToRelationshipButton extends RelationshipButton
{
    protected $foreignKeyName; // 'client_id',
    protected $qualifiedForeignKeyName; // UserVariableRelationship::FIELD_CLIENT_ID,
    protected $ownerKeyName; // 'client_id',
    protected $qualifiedOwnerKeyName; // UserVariableRelationship::FIELD_CLIENT_ID,
    protected $childClass;
    /**
     * BelongsToRelationshipButton constructor.
     * @param null $methodOrModel
     * @param BelongsTo|null $relation
     */
    public function __construct($methodOrModel = null, BelongsTo $relation = null){
        if(!$methodOrModel){return;}
        parent::__construct($methodOrModel, $relation);
        $relation = $this->getButtonRelation();
        try {
            $relatedModel = $this->getRelated();
        } catch (NoIdException $e) {
            /** @var \LogicException $e */
            throw $e;
        }
        $this->foreignKeyName = $relation->getForeignKeyName();
        $this->ownerKeyName = $relation->getOwnerKeyName();
        $this->qualifiedForeignKeyName = $relation->getQualifiedForeignKeyName();
        $this->qualifiedOwnerKeyName = $relation->getQualifiedOwnerKeyName();
        $this->qualifiedParentKeyName = $relation->getQualifiedParentKeyName();
        $this->childClass = get_class($this->getChild());
        $this->setTextAndTitle($this->generateRelationshipTitle());
        if($relatedIds = $this->getRelatedIds()){
            /** @var BaseModel $relatedClass */
            $relatedClass = $this->getRelatedClass();
            if(count($relatedIds) === 1){
                foreach($relatedIds as $key => $value){
                    $url = $relatedClass::generateAstralShowUrl($value);
                    $this->setUrl($url);
					if($relatedModel->hasId()){
						$this->setUrl($relatedModel->getUrl());
						$this->setImage($relatedModel->getImage());
						$this->setTextAndTitle($relatedModel->getTitleAttribute());
						$this->setTooltip($this->getTooltip());
						return;
					}
                }
            } else {
                $url = $relatedClass::generateAstralIndexUrl($relatedIds);
                $this->setUrl($url);
            }
        }
        $this->setFontAwesome($relatedModel->getFontAwesome());
        $this->getTooltip();
    }
    /**
     * @return string
     */
    public function generateRelationshipTitle():string{
        if(!$relatedIds = $this->getRelatedIds()){
            return $this->relationshipTitle;
        }
        $relatedModel = $this->getRelated();
        if($relatedModel->hasId()){
            $this->subtitle = $this->relationshipTitle;
            return $relatedModel->getTitleAttribute();
        }
        /** @var BaseModel $relatedClass */
        $relatedClass = $this->getRelatedClass();
        $fromMemory = $relatedClass::findInMemory($relatedIds);
        if($fromMemory){
            $this->subtitle = $this->relationshipTitle;
            return $fromMemory->getTitleAttribute();
        }
        return $this->relationshipTitle;
    }
    /**
     * @return BaseModel|Model
     */
    protected function getChild():BaseModel{
        return $this->getButtonRelation()->getChild();
    }
    /**
     * @return BelongsTo
     */
    public function getButtonRelation(): Relation{
        return parent::getButtonRelation();
    }
    /**
     * @return array
     */
    protected function getForeignKeyNames(): array {
        $relation = $this->getButtonRelation();
        if($useRelation = true){
            $keys = $relation->getForeignKeyName();
            if(is_array($keys)){
                return $keys;
            }
            return [$keys];
        } else {
            if(is_array($this->foreignKeyName)){
                return $this->foreignKeyName;
            }
            return [$this->foreignKeyName];
        }
    }
    /**
     * @return string
     * @throws NoIdException
     */
    public function getTooltip(): ?string{
        $related = $this->getRelated();
        $parent = $this->getParent();
        $tooltip = $this->tooltip;
        if($related->hasId()){
            $tooltip = $related->getTitleAttribute()." is the $this->relationshipTitle for this ".$parent->getClassNameTitle();
        } elseif($relatedId = $this->getRelatedIds()) {
            if(!$tooltip){le('!$tooltip');}
        } else {
            $tooltip = "The $this->relationshipTitle for this ".$parent->getClassNameTitle()." has not been determined.  ";
        }
        $this->setTooltip($tooltip);
        return $tooltip;
    }
    public function getImage(): string{
        $related = $this->getRelated();
        if($related && $related->hasId()){
            return $this->image = $related->getImage();
        }
        return $this->image;
    }
    /**
     * @return BaseModel|Model
     */
    protected function getRelated(){
        $relation = $this->getButtonRelation();
	    /** @var BaseModel $related */
	    $related = $relation->getRelated();
        if(!$related->hasId()){
            if($ids = $this->getRelatedIds()){
                if(count($ids) === 1){
                    $values = array_values($ids);
                    $id = array_shift($values);
                    if(!$id){
                        throw new NoIdException($this->getParentClass(),
                            "No ".$relation->getForeignKeyName());
                    }
                    $fromMemory = $related->findInMemory($id);
                } else {
                    $fromMemory = $related->findInMemoryWhere($ids);
                }
                if($fromMemory){
                    return $fromMemory;
                }
            }
        }
        return $related;
    }
    /**
     * @return array
     */
    public function getRelatedIds(): array {
        $parent = $this->getParent();
        $ids = [];
        $names = $this->getForeignKeyNames();
        foreach($names as $name){
            $ids[$name] = $parent->getAttribute($name);
            if(is_array($ids[$name])){le("is_array(ids[name]");}
        }
        return $ids;
    }
}
