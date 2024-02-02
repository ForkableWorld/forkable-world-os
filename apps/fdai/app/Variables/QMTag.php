<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables;
use App\Properties\Base\BaseClientIdProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use LogicException;
use App\Exceptions\InvalidTagCategoriesException;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMException;
use App\DataSources\QMConnector;
use App\Storage\DB\QMQB;
use App\Slim\Model\DBModel;
use App\Types\TimeHelper;
use App\Logging\QMLog;
use App\VariableCategories\ActivitiesVariableCategory;
use App\VariableCategories\BooksVariableCategory;
use App\VariableCategories\CausesOfIllnessVariableCategory;
use App\VariableCategories\ElectronicsVariableCategory;
use App\VariableCategories\EnvironmentVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\GoalsVariableCategory;
use App\VariableCategories\LocationsVariableCategory;
use App\VariableCategories\MusicVariableCategory;
use App\VariableCategories\NutrientsVariableCategory;
use App\VariableCategories\PaymentsVariableCategory;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\VariableCategories\SoftwareVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;
class QMTag extends DBModel {
    public const FIELD_ID = 'id';
    public const FIELD_TAGGED_VARIABLE_ID = 'tagged_variable_id';
    public const FIELD_TAG_VARIABLE_ID = 'tag_variable_id';
    public const FIELD_NUMBER_OF_DATA_POINTS = 'number_of_data_points';
    public const FIELD_STANDARD_ERROR = 'standard_error';
    public const FIELD_TAG_VARIABLE_UNIT_ID = 'tag_variable_unit_id';
    public const FIELD_TAGGED_VARIABLE_UNIT_ID = 'tagged_variable_unit_id';
    public const FIELD_CONVERSION_FACTOR = 'conversion_factor';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_DELETED_AT = 'deleted_at';
    public $id;
    public $taggedVariableId;
    public $tagVariableId;
    public $conversionFactor;
    public $clientId;
    /**
     * CommonTag constructor.
     * @param $id
     * @param $taggedVariableId
     * @param $tagVariableId
     * @param $conversionFactor
     * @param $clientId
     */
    public function __construct(int $id = null, int $taggedVariableId = null, int $tagVariableId = null,
                                float $conversionFactor = null, string $clientId = null){
        if(!$taggedVariableId){return;}
        $this->id = $id;
        $this->taggedVariableId = $taggedVariableId;
        $this->tagVariableId = $tagVariableId;
        $this->conversionFactor = $conversionFactor;
        $this->clientId = $clientId;
    }
    /**
     * @param int $taggedChildrenFoodVariableId
     * @param int $tagParentIngredientVariableId
     * @param float $conversionFactor
     * @param string $clientId
     * @return int
     * @throws InvalidTagCategoriesException
     */
    public static function updateOrInsert(int $taggedChildrenFoodVariableId, int $tagParentIngredientVariableId,
                                          float $conversionFactor, string $clientId = null){
        $clientId = static::fallbackToConnectorOrRequestClientId($clientId);
        if($taggedChildrenFoodVariableId === $tagParentIngredientVariableId){
            throw new QMException(QMException::CODE_BAD_REQUEST, 'TagVariableId cannot be the same as taggedVariableId');
        }
        $tag = QMCommonVariable::find($tagParentIngredientVariableId);
        $tagged = QMCommonVariable::find($taggedChildrenFoodVariableId);
        $existingTag = static::getExistingTag($taggedChildrenFoodVariableId, $tagParentIngredientVariableId);
        if($existingTag){
            if((float)$existingTag->conversion_factor === (float)$conversionFactor){
                QMLog::info("Existing common tag $tag to $tagged conversion factor has not changed from $conversionFactor");
                return false;
            }
            $tagId = static::updateExistingTag($existingTag, $conversionFactor, $tag, $tagged, $clientId);
        }else{
            $tagId = static::insertNewTag($taggedChildrenFoodVariableId,
                    $tagParentIngredientVariableId,
                    $conversionFactor,
                    $clientId);
        }
        return $tagId;
    }
    /**
     * @param int $taggedChildrenFoodVariableId
     * @param int $tagParentIngredientVariableId
     * @return mixed|QMQB
     */
    protected static function getExistingTag(int $taggedChildrenFoodVariableId, int $tagParentIngredientVariableId){
        $tag = QMCommonVariable::find($tagParentIngredientVariableId);
        $tagged = QMCommonVariable::find($taggedChildrenFoodVariableId);
        $taggedRows = $tag->getCommonTaggedRows();
        $existingTag = Arr::first($taggedRows,
            function($row) use ($taggedChildrenFoodVariableId, $tagParentIngredientVariableId){
                return $row->tagged_variable_id === $taggedChildrenFoodVariableId &&
                    $row->tag_variable_id = $tagParentIngredientVariableId;
            });
        $tagRows = $tagged->getCommonTaggedVariables();
        if(!$existingTag){
            $existingTag = Arr::first($tagRows,
                function($row) use ($taggedChildrenFoodVariableId, $tagParentIngredientVariableId){
                    return $row->taggedVariableId === $taggedChildrenFoodVariableId &&
                        $row->tagVariableId = $tagParentIngredientVariableId;
                });
        }
        if(!$existingTag){
            $existingTag =
                static::readonly()
                    ->where(static::FIELD_TAGGED_VARIABLE_ID, $taggedChildrenFoodVariableId)
                    ->where(static::FIELD_TAG_VARIABLE_ID, $tagParentIngredientVariableId)
                    ->first();
        }
        return $existingTag;
    }
    /**
     * @param int $ingredientId
     * @param int $foodId
     * @param $conversionFactor
     * @param string $clientId
     * @return int
     * @throws InvalidTagCategoriesException
     */
    public static function addIngredientTag(int $ingredientId, int $foodId, $conversionFactor, string $clientId){
        return static::updateOrInsert($foodId, $ingredientId, $conversionFactor, $clientId);
    }
    /**
     * @param int $tagVariableId
     * @return array|static[]
     * @internal param int $userTagVariableId
     */
    public static function getForTagVariable($tagVariableId){
        return static::readonly()->where(static::FIELD_TAG_VARIABLE_ID, $tagVariableId)->getArray();
    }
    /**
     * @param int $taggedChildrenFoodVariableId
     * @param int $tagParentIngredientVariableId
     * @return bool
     * @throws InvalidTagCategoriesException
     */
    public static function validateTagCategories(int $taggedChildrenFoodVariableId, int $tagParentIngredientVariableId){
        $tag = QMCommonVariable::findByNameIdOrSynonym($tagParentIngredientVariableId);
        $tagged = QMCommonVariable::findByNameIdOrSynonym($taggedChildrenFoodVariableId);
        $tagCategory = $tag->getVariableCategoryName();
        if($tagCategory === PaymentsVariableCategory::NAME){return true;}
        $taggedCategory = $tagged->getVariableCategoryName();
        if($taggedCategory === PaymentsVariableCategory::NAME){return true;}
        if($taggedCategory === $tagCategory){return true;}
        $acceptableTagTaggedPairs = [];
        $acceptableTagTaggedPairs[] = [ActivitiesVariableCategory::NAME => LocationsVariableCategory::NAME];
        $acceptableTagTaggedPairs[] = [ActivitiesVariableCategory::NAME => PhysicalActivityVariableCategory::NAME];
        $acceptableTagTaggedPairs[] = [FoodsVariableCategory::NAME => TreatmentsVariableCategory::NAME];
        $acceptableTagTaggedPairs[] = [NutrientsVariableCategory::NAME => FoodsVariableCategory::NAME];
        $acceptableTagTaggedPairs[] = [NutrientsVariableCategory::NAME => TreatmentsVariableCategory::NAME];
        $acceptableTagTaggedPairs[] = [TreatmentsVariableCategory::NAME => FoodsVariableCategory::NAME];
        $acceptableTagTaggedPairs[] = [TreatmentsVariableCategory::NAME => BooksVariableCategory::NAME];
        $acceptableTagTaggedPairs[] = [BooksVariableCategory::NAME => TreatmentsVariableCategory::NAME];
        foreach($acceptableTagTaggedPairs as $pair){
            $acceptableTag = key($pair);
            $acceptableTagged = $pair[$acceptableTag];
            if($tagCategory === $acceptableTag && $taggedCategory === $acceptableTagged){
                return true;
            }
        }
        throw new InvalidTagCategoriesException($tag, $tagged);
    }
    /**
     * @param int $tagParentIngredientVariableId
     * @param int $taggedChildrenFoodVariableId
     * @return QMCommonTag
     */
    public static function getTagRow(int $tagParentIngredientVariableId, int $taggedChildrenFoodVariableId){
        $qb = static::writable()
            ->where(static::FIELD_TAGGED_VARIABLE_ID, $taggedChildrenFoodVariableId)
            ->where(static::FIELD_TAG_VARIABLE_ID, $tagParentIngredientVariableId);
        $row = $qb->first();
        $row = static::instantiateIfNecessary($row);
        return $row;
    }
    /**
     * @param int $tagParentIngredientVariableId
     * @param int $taggedChildrenFoodVariableId
     * @param string $reason
     * @return Model|Builder|mixed|null
     */
    public static function delete(int $tagParentIngredientVariableId, int $taggedChildrenFoodVariableId, string $reason){
        $qb = static::writable()
            ->where(static::FIELD_TAGGED_VARIABLE_ID, $taggedChildrenFoodVariableId)
            ->where(static::FIELD_TAG_VARIABLE_ID, $tagParentIngredientVariableId);
        $row = $qb->first();
        if(!$row){
            throw new NotFoundException("No common tags with tagged id $taggedChildrenFoodVariableId and tag id $tagParentIngredientVariableId exist!");
        }
        $message = "Deleting common tag created ".TimeHelper::timeSinceHumanString($row->created_at)." because $reason";
		QMLog::errorIfProduction($message);
        $result = $qb->delete();
        if($result){
            static::updateAnalysisSettingsModifiedAt($tagParentIngredientVariableId,
                $taggedChildrenFoodVariableId,
                $reason);
        }
        return $result;
    }
    /**
     * @param string $tagCategory
     * @param string $taggedCategory
     * @return bool
     */
    public static function stupidCategoryPairs(string $tagCategory, string $taggedCategory){
        $taggedCategoryTagCategory = [
            [
                ActivitiesVariableCategory::NAME,
                EnvironmentVariableCategory::NAME
            ],
            [
                ElectronicsVariableCategory::NAME,
                SoftwareVariableCategory::NAME
            ],
            [
                EnvironmentVariableCategory::NAME,
                ActivitiesVariableCategory::NAME
            ],
            [
                FoodsVariableCategory::NAME,
                MusicVariableCategory::NAME
            ],
            [
                GoalsVariableCategory::NAME,
                ActivitiesVariableCategory::NAME
            ],
            [
                LocationsVariableCategory::NAME,
                MusicVariableCategory::NAME
            ],
            [
                LocationsVariableCategory::NAME,
                TreatmentsVariableCategory::NAME
            ],
            [
                MusicVariableCategory::NAME,
                LocationsVariableCategory::NAME
            ],
            [
                SoftwareVariableCategory::NAME,
                ElectronicsVariableCategory::NAME
            ],
            [
                SymptomsVariableCategory::NAME,
                TreatmentsVariableCategory::NAME
            ],
            [
                TreatmentsVariableCategory::NAME,
                LocationsVariableCategory::NAME
            ],
            [
                TreatmentsVariableCategory::NAME,
                SymptomsVariableCategory::NAME
            ],
            [
                ActivitiesVariableCategory::NAME,
                FoodsVariableCategory::NAME
            ],
            [
                FoodsVariableCategory::NAME,
                ActivitiesVariableCategory::NAME
            ],
            [
                CausesOfIllnessVariableCategory::NAME,
                ActivitiesVariableCategory::NAME
            ],
            [
                ActivitiesVariableCategory::NAME,
                CausesOfIllnessVariableCategory::NAME
            ],
        ];
        foreach($taggedCategoryTagCategory as $pair){
            if($pair === [$taggedCategory, $tagCategory]){
                return true;
            }
        }
        return false;
    }
    /**
     * @param int $tagId
     * @param float $conversionFactor
     * @param QMCommonVariable $tag
     * @param QMCommonVariable $tagged
     * @param string $clientId
     * @return mixed
     */
    protected static function updateExistingTag(int $tagId,
                                              float $conversionFactor,
                                              QMCommonVariable $tag,
                                              QMCommonVariable $tagged,
                                              string $clientId){
        QMLog::info("Updating existing common tag $tag to $tagged with $conversionFactor conversion factor");
        $result = static::writable()
            ->where('id', $tagId)->update([
                static::FIELD_CONVERSION_FACTOR => $conversionFactor,
                'client_id'             => $clientId,
                'updated_at'            => date('Y-m-d H:i:s')
            ]);
        if(!$result){
            le("FAILED Updating existing common tag $tag to $tagged with $conversionFactor conversion factor");
        }
        $tag->calculateNumberCommonTaggedBy();
        $tagged->calculateNumberOfCommonTags();
        return $tagId;
    }
    /**
     * @param $clientId
     * @return string
     */
    protected static function fallbackToConnectorOrRequestClientId(string $clientId = null): ?string{
        if(!$clientId && QMConnector::getCurrentlyImportingConnector()){
            $clientId = QMConnector::getCurrentlyImportingConnector()->name;
        }else{
            $clientId = BaseClientIdProperty::fromRequest(false);
        }
        return $clientId;
    }
    /**
     * @param int $taggedChildrenFoodVariableId
     * @param int $tagParentIngredientVariableId
     * @param $conversionFactor
     * @param $clientId
     * @return int
     * @throws InvalidTagCategoriesException
     */
    protected static function insertNewTag(int $taggedChildrenFoodVariableId,
                                         int $tagParentIngredientVariableId,
                                         $conversionFactor,
                                         $clientId): int{
        $tag = QMCommonVariable::find($tagParentIngredientVariableId);
        $tagged = QMCommonVariable::find($taggedChildrenFoodVariableId);
        QMLog::info("adding common tag $tag to $tagged with $conversionFactor conversion factor");
        static::validateTagCategories($taggedChildrenFoodVariableId, $tagParentIngredientVariableId);
        $tagId = static::writable()->insertGetId([
            'client_id'                    => $clientId,
            static::FIELD_CONVERSION_FACTOR        => $conversionFactor,
            static::FIELD_TAG_VARIABLE_ID    => $tagParentIngredientVariableId,
            static::FIELD_TAGGED_VARIABLE_ID => $taggedChildrenFoodVariableId,
            'created_at'                   => date('Y-m-d H:i:s'),
            'updated_at'                   => date('Y-m-d H:i:s')
        ]);
        $tag->calculateNumberCommonTaggedBy();
        $tag->unsetAllTagTypes(); // Much faster than setAllCommonTagVariableTypes
        $tagged->calculateNumberOfCommonTags();
        $tagged->unsetAllTagTypes();
        return $tagId; // Much faster than setAllCommonTagVariableTypes
    }
    /**
     * @param int $tagParentIngredientVariableId
     * @param int $taggedChildrenFoodVariableId
     * @param string $reason
     */
    protected static function updateAnalysisSettingsModifiedAt(int $tagParentIngredientVariableId,
                                                             int $taggedChildrenFoodVariableId,
                                                             string $reason): void{
        $v = QMCommonVariable::findByNameOrId($taggedChildrenFoodVariableId);
        $v->setAnalysisSettingsModifiedAt(true, $reason);
        $v = QMCommonVariable::findByNameOrId($tagParentIngredientVariableId);
        $v->setAnalysisSettingsModifiedAt(true, $reason);
    }
    /**
     * @param QMQB $qb
     */
    public static function addColumns($qb){
        $qb->columns[] = static::TABLE.'.'.static::FIELD_CONVERSION_FACTOR.' as tagConversionFactor';
        $qb->columns[] = static::TABLE.'.'.static::FIELD_TAGGED_VARIABLE_ID.' as taggedVariableId';
        $qb->columns[] = static::TABLE.'.'.static::FIELD_TAG_VARIABLE_ID.' as tagVariableId';
    }
}
