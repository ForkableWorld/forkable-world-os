<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasOne;
class HasOneRelationshipButton extends RelationshipButton
{
    protected $relatedClass; // Application::class,
    protected $relationName; // 'application'
    protected $foreignKeyName; // Application::FIELD_CLIENT_ID,
    protected $qualifiedForeignKeyName;
    protected $localKeyName;
    /**
     * HasOneRelationshipButton constructor.
     * @param string|BaseModel $methodOrModel
     * @param HasOne $relation
     */
    public function __construct($methodOrModel = null, HasOne $relation = null){
        if(!$methodOrModel){return;}
        parent::__construct($methodOrModel,$relation);
        $relation = $this->getButtonRelation();
        $this->qualifiedForeignKeyName = $relation->getQualifiedForeignKeyName();
        $this->foreignKeyName = $relation->getForeignKeyName();
        $this->localKeyName = $relation->getLocalKeyName();
        $parent = $this->getParent();
        if($parent->hasId()){
            $relatedClass = $this->getRelatedClass();
            $url = $relatedClass::generateDataLabIndexUrl([$this->foreignKeyName => $parent->getId()]);
            $this->setUrl($url);
        }
    }
}
