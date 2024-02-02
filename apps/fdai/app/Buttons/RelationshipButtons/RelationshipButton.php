<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Buttons\QMButton;
use App\Logging\QMLog;
use App\Types\QMStr;
use App\Repos\QMAPIRepo;
class RelationshipButton extends QMButton
{
    const BUTTONS_FOLDER = parent::BUTTONS_FOLDER.'/RelationshipButtons/';
    protected $parentClass;
    protected $qualifiedParentKeyName;
    protected $relatedClass; // OAClient::class,
    public $methodName; // 'oa_client'
    public $relationshipType; // 'BelongsTo',
    public $interesting = true;
    protected $related; // 'BelongsTo',
    /**
     * @var Relation
     */
    protected $relation;
    /**
     * @var string
     */
    public $relationshipTitle;
    /**
     * RelationshipButton constructor.
     * @param null $methodOrModel
     * @param Relation|null $relation
     */
    public function __construct($methodOrModel = null, Relation $relation = null){
        if(!$methodOrModel){return;}
        if($methodOrModel instanceof BaseModel){
            $this->methodName = $methodName = $this->methodName;
            try {
                $relation = $methodOrModel->$methodName();
            } catch (\Throwable $e){
                $relation = $methodOrModel->$methodName();
            }
        } else {
            $this->methodName = $methodOrModel;
        }
        /** @var BaseModel $related */
        $related = $relation->getRelated();
        $this->relatedClass = get_class($relation->getRelated());
        $this->parentClass = get_class($relation->getParent());
        $this->relation = $relation;
        $this->qualifiedParentKeyName = $relation->getQualifiedParentKeyName();
        $this->relationshipType = get_class($relation);
        parent::__construct();
        $title = $this->title;
        if(!$title){
            $title = str_replace("_id", "", $this->methodName);
            $title = QMStr::snakeToTitle($title);
            $title = QMStr::removeDBPrefixes($title);
            $this->setTextAndTitle($title);
        }
        $this->relationshipTitle = $this->title;
        if(!$this->image){$this->setImage($related->getImage());}
        if(!$this->fontAwesome){$this->setFontAwesome($related->getFontAwesome());}
        if(!$this->tooltip){
            $this->setTooltip($related->getSubtitleAttribute());
        }
        if(!$this->color){$this->setBackgroundColor($related->getColor());}
        $title = $this->getTitleAttribute();
        $slug = QMStr::slugify($title);
        $this->setId($slug);
    }
    public static function generateRelationshipButtons(){
        $classes = BaseModel::getClassNames();
        //$classes = [User::class];
        foreach($classes as $class){
            /** @var BaseModel $model */
            $model = new $class();
            $model->generateRelationshipButtons();
        }
    }
    public static function saveAndCommitHardCodedRelationships(){
        QMAPIRepo::createFeatureBranch(__FUNCTION__);
        self::generateRelationshipButtons();
        QMAPIRepo::addFilesInFolder(self::BUTTONS_FOLDER);
        QMAPIRepo::commitAndPush(QMStr::camelToTitle(__FUNCTION__));
    }
    /**
     * @return Relation
     */
    public function getButtonRelation(): Relation{
        return $this->relation;
    }
    /**
     * @return string|BaseModel
     */
    public function getParentClass(): string{
        return $this->parentClass;
    }
    /**
     * @return BaseModel|string
     */
    protected function getRelatedClass(): string {
        return $this->relatedClass;
    }
    /**
     * @inheritDoc
     */
    public static function getHardCodedDirectory(): string{
        return self::BUTTONS_FOLDER.'/'.static::getParentShortClassName();
    }
    protected static function getParentShortClassName():string{
        return QMStr::toShortClassName((new static())->parentClass);
    }
    protected function getHardCodedShortClassName(): string {
        $name = $this->getParentShortClassName().str_replace(' ', '', $this->title)."Button";
        return QMStr::toClassName($name);
    }
    /**
     * @return BaseModel|Model
     */
    public function getModel(): ?BaseModel{
        if(!$this->relation){
            return null;
        }
        return $this->getButtonRelation()->getRelated();
    }
    /**
     * @return BaseModel
     */
    public function getModelClass():?string{
        return $this->relatedClass;
    }
    /**
     * @param string|BaseModel $modelClass
     * @return string
     */
    protected function getHardCodedPropertiesString(string $modelClass = null): string{
        $properties = '';
        $parentClass = $this->getParentClass();
        $relatedClass = $this->getRelatedClass();
        foreach($this as $key => $value){
            if(in_array($key, ['html'])){continue;}
            if($value !== null && !is_object($value)){
                try {
                    $exported = $relatedClass::getConstantStringForValue($value, false);
                    if(!$exported){$exported = $parentClass::getConstantStringForValue($value, true);}
                    if($value === $parentClass){$exported = QMStr::toShortClassName($parentClass)."::class";}
                    if($value === $relatedClass){$exported = QMStr::toShortClassName($relatedClass)."::class";}
                    $properties .= "\tpublic $$key = ".$exported.";\n";
                } catch (\Throwable $e) { // Catch closure failures
                    QMLog::info(__METHOD__.": ".$e->getMessage());
                    continue;
                }
            }
        }
        return $properties;
    }
    /**
     * @return string
     */
    protected function getUseStatements(): string{
        $use = "use ".static::class.";\n";
        $use .= "use ".$this->getRelatedClass().";\n";
        if($this->getRelatedClass() !== $this->getParentClass()){
            $use .= "use ".$this->getParentClass().";";
        }
        return $use;
    }
    protected function getParentTitle():string{
        if($p = $this->getParent()){
            return $p->getTitleAttribute();
        }
        $class = $this->getParentClass();
        return QMStr::classToTitle($class);
    }
    /**
     * @return BaseModel|Model
     */
    protected function getParent():?BaseModel{
        if($this->relation){
            return $this->getButtonRelation()->getParent();
        }
        return null;
    }
    /**
     * @return BaseModel|Model
     */
    protected function getRelated(){
        return $this->getButtonRelation()->getRelated();
    }
    public static function getKey(): string{
        return (new static(null))->methodName;
    }
}
