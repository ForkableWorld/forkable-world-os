<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection NullPointerExceptionInspection */
/** @noinspection TypeUnsafeComparisonInspection */
namespace App\Variables;
use App\Models\UserTag;
use App\Properties\Base\BaseClientIdProperty;
use LogicException;
use App\Slim\QMSlim;
use App\Exceptions\BadRequestException;
use App\Exceptions\UserTagNotFoundException;
use App\Exceptions\UserVariableNotFoundException;
use App\Slim\Middleware\QMAuth;
use App\Utils\APIHelper;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\DB\Writable;
use App\Types\QMArr;
use App\Logging\QMLog;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use App\CodeGenerators\Swagger\SwaggerDefinition;
use App\Slim\Model\QMUnit;
use App\Slim\Model\User\QMUser;
use Throwable;
/**
 * @mixin UserTag
 */
class QMUserTag extends QMTag {
    public const TABLE = 'user_tags';
    public const FIELD_USER_ID = 'user_id';
    public CONST FIELD_TAG_VARIABLE_ID = 'tag_variable_id';
    public CONST FIELD_TAGGED_VARIABLE_ID = 'tagged_variable_id';
	public const FIELD_TAG_USER_VARIABLE_ID = 'tag_user_variable_id';
	public const FIELD_TAGGED_USER_VARIABLE_ID = 'tagged_user_variable_id';
    public CONST FIELD_CONVERSION_FACTOR = 'conversion_factor';
    public $id;                            // tag id
    public $userId;                    // id of user for which tag applies
    public $taggedVariableId;            // tagged_variable_id
    public $tagVariableId;
    public $conversionFactor;            // tag_variable_id
    /**
     * UserTag constructor.
     * @param $id
     * @param $userId
     * @param $tagVariableId
     * @param $taggedVariableId
     * @param $conversionFactor
     * @param $clientId
     */
    public function __construct(int $id = null, int $userId = null, int $tagVariableId = null, 
                                int $taggedVariableId = null,
                                float $conversionFactor = null, string $clientId = null){
        $this->id = $id;
        $this->tagVariableId = $tagVariableId;
        $this->taggedVariableId = $taggedVariableId;
        $this->conversionFactor = $conversionFactor;
        parent::__construct($userId, $clientId);
    }
    /**
     * @return string
     */
    private static function getUserOrCommonTagTableName(): string {
        $table = self::TABLE;
        if(QMAuth::isAdmin()){$table = QMCommonTag::TABLE;}
        return $table;
    }
    /**
     * @param int $userId
     * @param int $userTagParentIngredientCategoryVariableId
     * @param int $userTaggedChildFoodBrandVariableId
     * @param float $conversionFactor
     * @return array
     * @throws UserVariableNotFoundException
     * @internal param string $clientId
     */
    public static function addUserTagByVariableIds(int $userId, int $userTagParentIngredientCategoryVariableId,
                                                   int $userTaggedChildFoodBrandVariableId, $conversionFactor): array {
        [$userTagVariable, $userTaggedVariable] =
            self::validateUserTagRequest($userId, $userTagParentIngredientCategoryVariableId, $userTaggedChildFoodBrandVariableId, $conversionFactor);
        $tag = self::updateOrInsertUserTag($userId, $userTagParentIngredientCategoryVariableId, $userTaggedChildFoodBrandVariableId, $conversionFactor);
        self::postTagModificationRefreshAnalysisVerification($userId, $userTagVariable, $userTaggedVariable, __FUNCTION__);
        return [$userTagVariable, $userTaggedVariable];
    }
    /**
     * @param array $data
     * @param int $userId
     * @return int
     */
    private static function insert(array $data, int $userId){
        if(self::getUserOrCommonTagTableName() === self::TABLE){
            $data[self::FIELD_USER_ID] = $userId;
        } else {
			// Adding common tag
			unset($data[self::FIELD_TAG_USER_VARIABLE_ID]);
	        unset($data[self::FIELD_TAGGED_USER_VARIABLE_ID]);
		}
        return self::getUserOrCommonTagQb($userId)->insertGetId($data);
    }
    /**
     * @param int $userId
     * @return QMQB
     */
    private static function getUserOrCommonTagQb(int $userId): QMQB{
        $table = self::getUserOrCommonTagTableName();
        $qb = Writable::db()->table($table);
        if($table === self::TABLE){$qb->where(self::FIELD_USER_ID, $userId);}
        return $qb;
    }
    /**
     * @param int $userId
     * @param $params
     * @return array|static[]
     */
    public static function getUserTags($userId, $params){
        $db = ReadonlyDB::db();
        $qb = $db->table('user_tags');
        $qb->select(
            'id',
            'user_id as userId',
            'client_id as clientId',
            'tag_variable_id as userTagVariableId',
            'tagged_variable_id as userTaggedVariableId',
            'conversion_factor as conversionFactor',
            'updated_at as updatedAt',
            'created_at as createdAt');
        $qb->where('user_id', '=', $userId);
        $aliasToFieldNameMap = [
            'userTagVariableId'    => self::FIELD_TAG_VARIABLE_ID,
            'userTaggedVariableId' => self::FIELD_TAGGED_VARIABLE_ID,
            'updatedAt'            => 'updated_at',
            'createdAt'            => 'created_at',
            'deletedAt'            => 'deleted_at'
        ];
        QueryBuilderHelper::applyFilterParamsIfExist($qb, $aliasToFieldNameMap, $params);
        APIHelper::setPaginationHeaders($qb, 'id');
        QueryBuilderHelper::applyOffsetLimitSort($qb, $params, $aliasToFieldNameMap);
        $userTags = $qb->getArray();
        SwaggerDefinition::addOrUpdateSwaggerDefinition($userTags, __CLASS__);
        return $userTags;
    }
    /**
     * @param array $body
     * @param int|null $ingredientId
     * @param int|null $ingredientOfId
     * @param float $conversionFactor
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function addIngredientUserTag(array $body = [], int $ingredientId = null, int $ingredientOfId = null,
                                                float $conversionFactor = null): array {
        if(isset($body[0])){$body = $body[0];}
        if(isset($body['ingredientUserTagVariableId'])){$ingredientId = $body['ingredientUserTagVariableId'];}
        if(isset($body['ingredientOfUserTagVariableId'])){$ingredientOfId = $body['ingredientOfUserTagVariableId'];}
        if(isset($body['conversionFactor'])){$conversionFactor = $body['conversionFactor'];}
        if(!$ingredientId){throw new BadRequestException('ingredientUserTagVariableId should be included in body of request.');}
        if(!$ingredientOfId){throw new BadRequestException('ingredientOfUserTagVariableId should be included in body of request.');}
        if(!$conversionFactor){throw new BadRequestException('conversionFactor should be included in body of request.  ');}
        $user = QMAuth::getQMUserIfSet();
        $userId = $user->id;
        $instructions = "Include ingredientUserTagVariableId or ingredientOfUserTagVariableId parameters in variable search to get valid variables.";
        if($ingredientId == $ingredientOfId){
            throw new BadRequestException('ingredientUserTagVariableId cannot equal ingredientOfUserTagVariableId!  '.$instructions);
        }
        $variableParams['includeTags'] = true;
        $ingredientOfVariable = QMUserVariable::getByNameOrId($userId, $ingredientOfId, $variableParams);
        /** @var QMVariable $existingIngredientVariable */
        foreach($ingredientOfVariable->ingredientUserTagVariables as $existingIngredientVariable){
            if($ingredientId == $existingIngredientVariable->getVariableIdAttribute() &&
                $conversionFactor == $existingIngredientVariable->tagConversionFactor){
                throw new BadRequestException("ingredientUserTagVariableId ".$ingredientId.
                    " is already a an ingredient of ingredientOfUserTagVariableId ".
                    $ingredientOfId." and has the same conversion factor as that provided.  ".$instructions);
            }
        }
        $variables = self::addUserTagByVariableIds($userId, $ingredientId, $ingredientOfId, $conversionFactor);
        $variableParams['includeTags'] = true;
        $ingredientUserTagVariable = QMUserVariable::getByNameOrId($userId, $ingredientId, $variableParams);
        $ingredientOfVariable = QMUserVariable::getByNameOrId($userId, $ingredientOfId, $variableParams);
        return [
            'status'  => 201,
            'success' => true,
            'data'    => [
                'ingredientUserTagVariable'   => $ingredientUserTagVariable,
                'ingredientOfUserTagVariable' => $ingredientOfVariable,
            ]
        ];
    }
    /**
     * @return array
     */
    public static function getJoinLegacyParameters(){
        // Legacy => Current
        return [
            'parentVariableId' => 'currentVariableId',
            'joinedVariableId' => 'joinedUserTagVariableId'
        ];
    }
    /**
     * @param array $body
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function createJoinTag(array $body){
        if(isset($body[0])){$body = $body[0];}
        $body = QMStr::properlyFormatRequestParams($body, self::getJoinLegacyParameters());
        $joinId = QMArr::getValue($body, ['joinedUserTagVariableId']);
        $currentId = QMArr::getValue($body, ['currentVariableId', 'parentVariableId']);
        $user = QMAuth::getQMUserIfSet();
        $userId = $user->id;
        self::validateJoinRequest($userId, $joinId, $currentId);
        try {
            self::addUserTagByVariableIds($userId, $currentId,
                $joinId, 1);
        } catch (Throwable $e){ // Have to catch or we can't convert ingredient to duplicate join
            QMLog::info(__METHOD__.": ".$e->getMessage());
            throw $e;
        }
        self::addUserTagByVariableIds($userId, $joinId, $currentId, 1);
        $joinedUserTagVariable = QMUserVariable::getByNameOrId($userId, $joinId, ['includeTags' => true]);
        $parentVariable = QMUserVariable::getByNameOrId($userId, $currentId, ['includeTags' => true]);
        self::validateJoinedVariables($user, $parentVariable, $joinedUserTagVariable);
        return  [
            'status'  => 201,
            'success' => true,
            'data'    => [
                'joinedUserTagVariable' => $joinedUserTagVariable,
                'currentVariable'       => $parentVariable,
            ]
        ];
    }
    /**
     * @param $body
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function createParentUserTag($body){
        if(isset($body[0])){
            $body = $body[0];
        }
        if(!isset($body['parentUserTagVariableId'])){
            throw new BadRequestException('parentUserTagVariableId should be included in body of request.');
        }
        if(!isset($body['childUserTagVariableId'])){
            throw new BadRequestException('childUserTagVariableId should be included in body of request.');
        }
        $user = QMAuth::getQMUserIfSet();
        $userId = $user->id;
        $body['conversionFactor'] = 1;
        $instructions = "Include parentUserTagVariableId or childUserTagVariableId parameters in variable search to get valid variables.";
        if($body['parentUserTagVariableId'] == $body['childUserTagVariableId']){
            throw new BadRequestException('parentUserTagVariableId cannot equal childUserTagVariableId!  '.$instructions);
        }
        $variableParams['includeTags'] = true;
        $parentUserTagVariable = QMUserVariable::getByNameOrId($userId, $body['parentUserTagVariableId'], $variableParams);
        $childUserTagVariable = QMUserVariable::getByNameOrId($userId, $body['childUserTagVariableId'], $variableParams);
        if($parentUserTagVariable->getCommonUnit()->categoryName !== $childUserTagVariable->getCommonUnit()->categoryName){
            throw new BadRequestException('Parent must have same default unit category as child variable!  '.$instructions);
        }
        foreach($childUserTagVariable->parentUserTagVariables as $existingParentUserTagVariable){
            if($body['parentUserTagVariableId'] == $existingParentUserTagVariable->getVariableId()){
                throw new BadRequestException("parentUserTagVariableId ".$body['parentUserTagVariableId']." is already a parent of childUserTagVariableId ".$body['childUserTagVariableId'].".  ".$instructions);
            }
        }
        self::addUserTagByVariableIds($userId, $body['parentUserTagVariableId'], $body['childUserTagVariableId'], $body['conversionFactor']);
        $parentUserTagVariable = QMUserVariable::getByNameOrId($userId, $body['parentUserTagVariableId'], $variableParams);
        $childUserTagVariable = QMUserVariable::getByNameOrId($userId, $body['childUserTagVariableId'], $variableParams);
        if($user->isAdmin()){
            if(!$childUserTagVariable->parentCommonTagVariables){le("No parentCommonTagVariables!");}
            if(!$parentUserTagVariable->childCommonTagVariables){le("No childCommonTagVariables!");}
        } else {
            if(!$childUserTagVariable->parentUserTagVariables){le("No parentUserTagVariables!");}
            if(!$parentUserTagVariable->childUserTagVariables){le("No childUserTagVariables!");}
        }
        $response = [
            'status'  => 201,
            'success' => true,
            'data'    => [
                'parentUserTagVariable' => $parentUserTagVariable,
                'childUserTagVariable'  => $childUserTagVariable,
            ]
        ];
        return $response;
    }
    /**
     * @param $body
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function handleCreateUserTagRequest($body): array
    {
        if(isset($body[0])){$body = $body[0];}
        if(!isset($body['userTagVariableId'], $body['userTaggedVariableId'], $body['conversionFactor'])){
            throw new BadRequestException('userTagVariableId, conversionFactor, and userTaggedVariableId should be included in body of request.');
        }
        $user = QMAuth::getQMUser();
        $variableRequestParams['includeTags'] = true;
        self::addUserTagByVariableIds($user->getId(), $body['userTagVariableId'], $body['userTaggedVariableId'], $body['conversionFactor']);
        $tag = QMUserVariable::getOrCreateById($user->getId(), $body['userTagVariableId'], $variableRequestParams);
        $tagged = QMUserVariable::getOrCreateById($user->getId(), $body['userTaggedVariableId'], $variableRequestParams);
        $tag->getUserTaggedVariables();
        $tagged->getUserTagVariables();
        $response = [
            'status'  => 201,
            'success' => true,
            'data'    => [
                'userTagVariable'    => $tag,
                'userTaggedVariable' => $tagged,
            ]
        ];
        return $response;
    }
    /**
     * @param QMUserVariable $userTagVariable
     * @param QMUserVariable $userTaggedVariable
     * @throws BadRequestException
     */
    public static function throwExceptionIfIncompatibleVariables($userTagVariable, $userTaggedVariable){
        if(!QMUnit::variablesAreTagCompatible($userTagVariable, $userTaggedVariable)){
            throw new BadRequestException('You cannot tag a non-rating variable with a rating variable or vice versa!');
        }
    }
    /**
     * @param array $params
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function handleDeleteUserTagRequest(array $params = []): array
    {
        if(!$params){$params = QMSlim::getInstance()->getRequestJsonBodyAsArray(false);}
        if(!isset($params['userTagVariableId'], $params['userTaggedVariableId'])){
            throw new BadRequestException('userTagVariableId and userTaggedVariableId should be included in body of request.');
        }
        $userId = QMAuth::id();
        [$userTagVariable, $userTaggedVariable] =
            self::deleteRow($userId, $params['userTagVariableId'], $params['userTaggedVariableId'], __FUNCTION__);
        return ['userTagVariable' => $userTagVariable, 'userTaggedVariable' => $userTaggedVariable];
    }
    /**
     * @param int $userId
     * @param int $tagId
     * @param int $taggedId
     * @param string $reason
     * @return array
     * @throws UserVariableNotFoundException
     */
    private static function scheduleUpdatesAndReCorrelations(int $userId, int $tagId, int $taggedId, string $reason): array {
        $user = QMUser::find($userId);
        $user->unsetAllUserTags();
        $tagVariable = QMUserVariable::getByNameOrId($userId, $tagId);
        $tagVariable->unsetAllTagTypes();
        $taggedVariable = QMUserVariable::getByNameOrId($userId, $taggedId);
        $taggedVariable->unsetAllTagTypes();
        self::updateUserAnalysisSettingsModifiedAt($userId, $tagId, $taggedId, $reason);
        return [$tagVariable, $taggedVariable];
    }
    /**
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function handleDeleteIngredientUserTagRequest(){
        $app = QMSlim::getInstance();
        $body = $app->getRequestJsonBodyAsArray(false);
        if(!isset($body['ingredientUserTagVariableId'], $body['ingredientOfUserTagVariableId'])){
            throw new BadRequestException('ingredientUserTagVariableId and ingredientOfUserTagVariableId should be included in body of request.  ');
        }
        $userId = QMAuth::id();
        [$ingredientUserTagVariable, $ingredientOfUserTagVariable] = self::deleteRow($userId,
            $body['ingredientUserTagVariableId'], $body['ingredientOfUserTagVariableId'], __FUNCTION__);
        return [
            'ingredientUserTagVariable'   => $ingredientUserTagVariable,
            'ingredientOfUserTagVariable' => $ingredientOfUserTagVariable,
        ];
    }
    /**
     * @param int $userId
     * @param int $tagId
     * @param int $taggedId
     * @param string $reason
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function deleteRow(int $userId, int $tagId, int $taggedId, string $reason): array{
        $user = QMUser::find($userId);
        $success = self::writable()
            ->where(self::FIELD_TAG_VARIABLE_ID, $tagId)
            ->where(self::FIELD_TAGGED_VARIABLE_ID, $taggedId)
            ->where(self::FIELD_USER_ID, $userId)
            ->delete();
        if($user->isAdmin()){
            $result = QMCommonTag::delete($tagId, $taggedId, $reason." and $user is an admin");
            if(!$success){$success = $result;}
        }
        if(!$success){throw new UserTagNotFoundException("Could not delete tag variable id $tagId and tagged variable id $taggedId");}
        return self::scheduleUpdatesAndReCorrelations($userId, $tagId, $taggedId, $reason);
    }
    /**
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function handleDeleteJoinUserTagRequest(){
        $app = QMSlim::getInstance();
        $body = $app->getRequestJsonBodyAsArray(false);
        if(!isset($body['currentVariableId'], $body['joinedUserTagVariableId'])){
            throw new BadRequestException('currentVariableId and joinedUserTagVariableId should be included in body of request.  ');
        }
        $userId = QMAuth::id();
        [$joinedUserTagVariable, $currentVariable] =
            self::deleteUserJoin($userId, $body['joinedUserTagVariableId'], $body['currentVariableId'], __FUNCTION__);
        return [
            'joinedUserTagVariable' => $joinedUserTagVariable,
            'currentVariable'       => $currentVariable,
        ];
    }
    /**
     * @param int $userId
     * @param int $idOne
     * @param int $idTwo
     * @param string $reason
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function deleteUserJoin(int $userId, int $idOne, int $idTwo, string $reason){
        self::deleteRow($userId, $idOne, $idTwo, $reason);
        return self::deleteRow($userId, $idTwo, $idOne, $reason);
    }
    /**
     * @return array
     * @throws UserVariableNotFoundException
     */
    public static function handleDeleteParentUserTagRequest(){
        $body = QMSlim::getInstance()->getRequestJsonBodyAsArray(false);
        if(!isset($body['parentUserTagVariableId'], $body['childUserTagVariableId'])){
            throw new BadRequestException('parentUserTagVariableId and childUserTagVariableId should be included in body of request.');
        }
        $userId = QMAuth::id(true);
        [$parentUserTagVariable, $childUserTagVariable] =
            self::deleteRow($userId, $body['parentUserTagVariableId'], $body['childUserTagVariableId'], __FUNCTION__);
        return [
            'parentUserTagVariable' => $parentUserTagVariable,
            'childUserTagVariable'  => $childUserTagVariable,
        ];
    }
    /**
     * @param int $userId
     * @param $conversionFactor
     * @param static $existingTag
     * @throws BadRequestException
     */
    protected static function updateUserTag(int $userId, $conversionFactor, $existingTag): void{
        if(empty($conversionFactor)){
            throw new BadRequestException("Please provide conversion factor!");
        }
        $qb = self::getUserOrCommonTagQb($userId);
        if((float)$existingTag->conversion_factor === (float)$conversionFactor){
            self::validateUserTagRequest($userId, $existingTag->tag_variable_id, $existingTag->tagged_variable_id, $conversionFactor);
            throw new BadRequestException("Tag already exists and conversion factor has not changed!");
        }
        $qb->where(self::FIELD_ID, $existingTag->id)
            ->update([
                self::FIELD_CONVERSION_FACTOR => $conversionFactor,
                self::FIELD_CLIENT_ID         => BaseClientIdProperty::fromRequest(false),
                self::FIELD_UPDATED_AT        => now_at()
            ]);
    }
    /**
     * @param int $userId
     * @param int $userTagParentIngredientCategoryVariableId
     * @param int $userTaggedChildFoodBrandVariableId
     * @param $conversionFactor
     * @return self
     */
    protected static function insertUserTag(int $userId, int $userTagParentIngredientCategoryVariableId,
                                            int $userTaggedChildFoodBrandVariableId, $conversionFactor): self {
        if(empty($conversionFactor)){
            throw new BadRequestException("Please provide conversion factor!");
        }
		$userTagVariable = QMUserVariable::findOrCreateByNameOrId($userId, $userTagParentIngredientCategoryVariableId);
		$userTaggedVariable = QMUserVariable::findOrCreateByNameOrId($userId, $userTaggedChildFoodBrandVariableId);
        $arr = [
            'client_id'          => BaseClientIdProperty::fromRequest(false),
            self::FIELD_CONVERSION_FACTOR  => $conversionFactor,
            self::FIELD_TAG_VARIABLE_ID    => $userTagParentIngredientCategoryVariableId,
            self::FIELD_TAGGED_VARIABLE_ID => $userTaggedChildFoodBrandVariableId,
	        UserTag::FIELD_TAG_USER_VARIABLE_ID => $userTagVariable->id,
	        UserTag::FIELD_TAGGED_USER_VARIABLE_ID => $userTagVariable->id,
            'created_at'         => now_at(),
            'updated_at'         => now_at()
        ];
        $tagId = self::insert($arr, $userId);
        $tag = new static();
        $tag->populateFieldsByArrayOrObject($arr);
        $tag->id = $tagId;
        return $tag;
    }
    /**
     * @param int $userId
     * @param int $userTagParentIngredientCategoryVariableId
     * @param int $userTaggedChildFoodBrandVariableId
     * @param $conversionFactor
     * @return QMUserVariable[]
     * @throws BadRequestException
     */
    private static function validateUserTagRequest(int $userId, int $userTagParentIngredientCategoryVariableId, int $userTaggedChildFoodBrandVariableId, $conversionFactor): array{
        if(!is_numeric($conversionFactor)){
            throw new BadRequestException("Please provide numeric conversion factor!");
        }
        if($userTaggedChildFoodBrandVariableId === $userTagParentIngredientCategoryVariableId){
            throw new BadRequestException('userTagVariableId cannot be the same as userTaggedVariableId');
        }
        $userTagVariable = QMUserVariable::getOrCreateById($userId, $userTagParentIngredientCategoryVariableId, ['includeTags' => true]);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $userTagParentIngredientCategoryVariableName = $userTagVariable->name;
        $userTaggedVariable = QMUserVariable::getOrCreateById($userId, $userTaggedChildFoodBrandVariableId, ['includeTags' => true]);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $userTaggedChildFoodBrandVariableName = $userTaggedVariable->name;
        self::throwExceptionIfIncompatibleVariables($userTagVariable, $userTaggedVariable);
        return [
            $userTagVariable,
            $userTaggedVariable
        ];
    }
    /**
     * @param int $userId
     * @param $userTagVariable
     * @param $userTaggedVariable
     */
    private static function verifyTagCreation(int $userId, $userTagVariable, $userTaggedVariable): void{
        $user = QMUser::find($userId);
        if($user->isAdmin()){
            if(!$userTagVariable->commonTaggedVariables && !$userTaggedVariable->commonTagVariables){
                le("No commonTaggedVariables or commonTagVariables!");
            }
        }else if(!$userTagVariable->userTaggedVariables && !$userTaggedVariable->userTagVariables){
            le("No userTagVariables or userTaggedVariables!");
        }
    }
    /**
     * @param int $userId
     * @param int $userTagParentIngredientCategoryVariableId
     * @param int $userTaggedChildFoodBrandVariableId
     * @param $conversionFactor
     * @return QMUserTag
     * @throws BadRequestException
     */
    private static function updateOrInsertUserTag(int $userId, int $userTagParentIngredientCategoryVariableId,
                                                  int $userTaggedChildFoodBrandVariableId, $conversionFactor): self {
        [$userTagVariable, $userTaggedVariable] =
            self::validateUserTagRequest($userId, $userTagParentIngredientCategoryVariableId, $userTaggedChildFoodBrandVariableId, $conversionFactor);
        $qb = self::getUserOrCommonTagQb($userId);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $table = $qb->from;
        $tagId = null;
        $existingTag = $qb->where(self::FIELD_TAGGED_VARIABLE_ID, $userTaggedChildFoodBrandVariableId)
            ->where(self::FIELD_TAG_VARIABLE_ID, $userTagParentIngredientCategoryVariableId)
            ->first();
        if(!empty($existingTag)){
            self::updateUserTag($userId, $conversionFactor, $existingTag);
            return static::instantiateIfNecessary($existingTag);
        }else{
            return self::insertUserTag($userId,
                $userTagParentIngredientCategoryVariableId, $userTaggedChildFoodBrandVariableId, $conversionFactor);
        }
    }
    /**
     * @param int $userId
     * @param QMVariable $userTagVariable
     * @param QMVariable $userTaggedVariable
     * @param string $reason
     * @throws UserVariableNotFoundException
     */
    private static function postTagModificationRefreshAnalysisVerification(int $userId, QMVariable $userTagVariable,
                                                                           QMVariable $userTaggedVariable, string $reason): void{
        [$userTagVariable, $userTaggedVariable] =
            self::scheduleUpdatesAndReCorrelations($userId,
                $userTagVariable->getVariableIdAttribute(),
                $userTaggedVariable->getVariableIdAttribute(),
                $reason);
        // TODO: Why were these commented?
        /** @var QMVariable $userTagVariable */
        $userTagVariable->setAllCommonAndUserTagVariableTypes();
        /** @var QMVariable $userTaggedVariable */
        $userTaggedVariable->setAllCommonAndUserTagVariableTypes();
        if(QMAuth::isAdmin()){
            $cv = $userTaggedVariable->getCommonVariable();
            $cv->calculateNumberCommonTaggedBy();
            $num = $cv->calculateNumberOfCommonTags();
		if(!$num){le('!$num');}
            $cv->l()->save();
            $cv = $userTagVariable->getCommonVariable();
            $num = $cv->calculateNumberCommonTaggedBy();
		if(!$num){le('!$num');}
            $cv->calculateNumberOfCommonTags();
            $cv->l()->save();
        }
        self::verifyTagCreation($userId, $userTagVariable, $userTaggedVariable);
    }
    /**
     * @param QMUser|null $user
     * @param QMUserVariable $parentVariable
     * @param QMUserVariable $joinedUserTagVariable
     */
    private static function validateJoinedVariables(?QMUser $user, QMUserVariable $parentVariable, QMUserVariable $joinedUserTagVariable): void
    {
        if ($user->isAdmin()) {
            if (!$parentVariable->joinedCommonTagVariableIds) {
                le("No joinedCommonTagVariableIds!");
            }
            if (!$joinedUserTagVariable->joinedCommonTagVariableIds) {
                le("No joinedCommonTagVariableIds!");
            }
        } else {
            if (!$parentVariable->joinedUserTagVariables) {
                le("No joinedUserTagVariables!");
            }
            if (!$joinedUserTagVariable->joinedUserTagVariables) {
                le("No joinedUserTagVariables!");
            }
        }
    }
    /**
     * @param $userId
     * @param $joinId
     * @param $currentId
     * @throws UserVariableNotFoundException
     */
    private static function validateJoinRequest(int $userId, $joinId, $currentId){
        if (!$joinId) {
            throw new BadRequestException('joinedUserTagVariableId should be included in body of request.');
        }
        if (!$currentId) {
            throw new BadRequestException('Please include currentVariableId should be included in body of request.');
        }
        $instructions = "Include joinVariableId variable search to get valid variables.";
        if ($joinId == $currentId) {
            throw new BadRequestException('currentVariableId cannot equal joinedUserTagVariableId!  ' . $instructions);
        }
        $joinedUserTagVariable = QMUserVariable::getByNameOrId($userId, $joinId, ['includeTags' => true]);
        $currentVariable = QMUserVariable::getByNameOrId($userId, $currentId, ['includeTags' => true]);
        if ($joinedUserTagVariable->getCommonUnit()->categoryName !== $currentVariable->getCommonUnit()->categoryName) {
            throw new BadRequestException('Parent must have same default unit category as child variable!  ' . $instructions);
        }
        foreach ($currentVariable->getJoinedUserTagVariables() as $existingJoinedUserTagVariable) {
            if($joinId == $existingJoinedUserTagVariable->getVariableIdAttribute()){
                throw new BadRequestException(
                    "joinedUserTagVariableId $joinId is already a parent of currentVariableId $currentId.  $instructions");
            }
        }
    }
    /**
     * @param int $userId
     * @param int $tagParentIngredientVariableId
     * @param int $taggedChildrenFoodVariableId
     * @param string $reason
     */
    protected static function updateUserAnalysisSettingsModifiedAt(int $userId,
                                                                   int $tagParentIngredientVariableId,
                                                                   int $taggedChildrenFoodVariableId,
                                                                   string $reason): void{
        $v = QMUserVariable::findOrCreateByNameOrId($userId, $taggedChildrenFoodVariableId);
        $v->setAnalysisSettingsModifiedAt(true, $reason);
        $v = QMUserVariable::findOrCreateByNameOrId($userId, $tagParentIngredientVariableId);
        $v->setAnalysisSettingsModifiedAt(true, $reason);
    }
}
