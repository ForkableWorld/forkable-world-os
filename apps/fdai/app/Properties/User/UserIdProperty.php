<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Exceptions\UnauthorizedException;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\BaseModel;
use App\Models\User;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyUserIdProperty;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseIntegerIdProperty;
use App\Utils\AppMode;
use App\Types\QMArr;
use Illuminate\Database\Eloquent\Builder;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
class UserIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use UserProperty;
    public const USER_ID_IVY = 83110;
    public const USER_ID_CURE_TOGETHER = 2;
    public const NAME = User::FIELD_ID;
    const USER_ID_ADMIN = self::USER_ID_MIKE;
    private static array $testUserIds = [];
    public $name = self::NAME;
    private static array $testAndDeletedUserIds = [];
    public const SYNONYMS = [
        'user_id',
        'id',
        'ID'
    ];
    public const USER_ID_MedDRA = 3;
    public const USER_ID_POPULATION = 12000;
    public const USER_ID_PHYSICIAN = 8;
    public const USER_ID_TEST_USER = 18535;
    public const USER_ID_MIKE = 230;
    public const USER_ID_INACTIVE = 3092;
    public const USER_ID_DEMO = 1;
    public const USER_ID_THINK_BY_NUMBERS = 13000;
    public const USER_ID_ZERO = 0;
    public const USER_ID_SYSTEM = 7;
    public $table = User::TABLE;
    public $parentClass = User::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    /**
     * @param bool $throwException
     * @return int
     */
    public static function fromRequestOrAuthenticated(bool $throwException = false): ?int{
        $userId = self::fromRequest(false);
        if (!$userId) {
            if ($studyId = StudyIdProperty::fromRequestDirectly()) {
                $userId = StudyUserIdProperty::fromId($studyId);
            }
        } // Putting getUserIdFromStudyId in getUserIdParam function causes infinite loop
        if (!$userId) {
            $user = QMAuth::getQMUser();
            if ($user) {
                $userId = $user->id;
            }
        }
        if (!$userId && $throwException) {
            throw new UnauthorizedException("Could not get user id from request!");
        }
        return $userId;
    }
    /**
     * @param bool $throwException
     * @return int
     */
    public static function fromRequest(bool $throwException = false): ?int{
        $userId = QMRequest::getParamInt('userId', $throwException);
        if(!$userId){
            return null;
        }
        return $userId;
    }
    /**
     * @param bool $throwException
     * @return int
     */
    public static function fromQuery(bool $throwException = false): ?int{
        $userId = QMRequest::getQueryParam('userId', $throwException);
        if(!$userId){
            return null;
        }
        return (int)$userId;
    }
	/**
	 * @param $data
	 * @param bool $throwException
	 * @return int
	 */
    public static function fromDataOrRequest($data, bool $throwException = false): ?int{
        $id = QMArr::pluckValue($data, 'user_id');
        if(!$id){$id = QMAuth::id();}
		if(!$id && $throwException){
			throw new BadRequestException("Please provide user_id");
		}
        return $id;
    }
    /**
     * @param $data
     * @return User
     */
    public static function parentModelFromDataOrRequest($data): ?BaseModel{
        if($u = parent::parentModelFromDataOrRequest($data)){return $u;}
        if($u = QMAuth::getQMUser()){return $u->l();}
        return null;
    }
    /**
     * @return int[]
     */
    public static function getTestUserIds(): array{
        if($ids = static::$testUserIds){return $ids;}
        $ids = QMUser::readonly()
            ->where(User::FIELD_USER_LOGIN, \App\Storage\DB\ReadonlyDB::like(), "%testuser%")
            ->orWhere(User::FIELD_USER_LOGIN, \App\Storage\DB\ReadonlyDB::like(), "%test-user%")
            ->pluck(User::FIELD_ID);
        $arr = $ids->toArray();
        return static::$testUserIds = $arr;
    }
    /**
     * @return int
     */
    public static function getDebugUserId(): ?int
    {
        if (\App\Utils\Env::get('DEBUG_USER_ID')) {
            return (int)\App\Utils\Env::get('DEBUG_USER_ID');
        }
        return null;
    }
    /**
     * @return array
     */
    public static function getTestSystemAndDeletedUserIds(): array{
        if ($ids = self::$testAndDeletedUserIds) {
            if (!in_array(self::USER_ID_SYSTEM, $ids)) {le("No system user id!");}
            return $ids;
        }
        $ids = QMUser::readonly()
            ->where(User::FIELD_USER_LOGIN, \App\Storage\DB\ReadonlyDB::like(), "%testuser%")
            ->orWhere(User::FIELD_USER_LOGIN, \App\Storage\DB\ReadonlyDB::like(), "%test-user%")
            ->orWhere(User::FIELD_USER_LOGIN, \App\Storage\DB\ReadonlyDB::like(), "%deleted_%")
            ->pluck(User::FIELD_ID);
        $arr = $ids->toArray();
        if (!AppMode::isUnitTest()) {
            $arr[] = UserIdProperty::USER_ID_DEMO;
            $arr[] = self::USER_ID_CURE_TOGETHER;
        }
        $arr[] = UserIdProperty::USER_ID_ZERO;
        $arr[] = UserIdProperty::USER_ID_MedDRA;
        $arr[] = UserIdProperty::USER_ID_SYSTEM;
        $arr[] = UserIdProperty::USER_ID_POPULATION;
        return self::$testAndDeletedUserIds = $arr;
    }
    /**
     * @param Builder|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Relations\HasMany $qb
     */
    public static function excludeDeletedAndTestUsers($qb): void {
        $qb->whereNotIn(UserVariable::FIELD_USER_ID, self::getTestSystemAndDeletedUserIds());
    }
    public static function getPublicUserIds(): array{
		return [
			UserIdProperty::USER_ID_CURE_TOGETHER,
		    UserIdProperty::USER_ID_DEMO,
		    UserIdProperty::USER_ID_MedDRA,
		    UserIdProperty::USER_ID_MIKE,
		    UserIdProperty::USER_ID_POPULATION,
			UserIdProperty::USER_ID_SYSTEM,
			UserIdProperty::USER_ID_THINK_BY_NUMBERS,
			UserIdProperty::USER_ID_ZERO,
		];
    }
    public function getUserId(): ?int{
        return $this->getUser()->getUserId();
    }
    /**
     * @return int
     */
    public function getDefaultValue(): ?int{
        $id = $this->getDBValue();
        if($id === null){$id = QMAuth::getUserId();}
        return $id;
    }
}
