<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Exceptions\UnauthorizedException;
use App\Models\BaseModel;
use App\Models\User;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\HasUserFilter;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use App\Fields\Field;
use App\Http\Requests\AstralRequest;
use OpenApi\Generator;
class BaseUserIdProperty extends BaseIntegerIdProperty{
	use ForeignKeyIdTrait, HasUserFilter;
	public $autoIncrement = false;
	public $dbInput = 'bigInteger,false,true';
	public $dbType = 'bigint';
	public $default = Generator::UNDEFINED;
	public $unsigned = true;
	public $description = 'Unique ID indicating the owner of the record';
	public $example = UserIdProperty::USER_ID_DEMO;
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::USER;
	public $htmlType = 'text';
	public $image = ImageUrls::USER;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'user_id';
	public $canBeChangedToNull = false;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|numeric|min:1|max:1000000';
	public $title = 'User';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';

    /**
     * @param null $data
     * @return int
     * @throws UnauthorizedException
     */
    public static function getDefault($data = null): ?int{
        if(AppMode::isApiRequest()){
            return QMAuth::id();
        }
        return parent::getDefault($data);
    }
	/**
	 * @return int
	 */
	public function getExample(): int{
        return UserIdProperty::USER_ID_DEMO;
    }
    /**
     * @return User
     */
    public static function getForeignClass(): string{
        return User::class;
    }
	/**
	 * @param BaseModel|DBModel|array|object $data
	 * @return int|null|string
	 */
	public static function pluck($data){
        return parent::pluck($data);
    }
    public function showOnUpdate(): bool{return QMAuth::canSeeOtherUsers();}
    public function showOnCreate(): bool{return QMAuth::canSeeOtherUsers();}
    public function showOnDetail(): bool{return QMAuth::canSeeOtherUsers();}
    public function showOnIndex(): bool{
        if(!QMAuth::canSeeOtherUsers()){return false;}
        return AstralRequest::filterIsEveryone();
    }
	/**
	 * @return int|null
	 */
	public function getDefaultValue(): ?int{
        $id = $this->getDBValue();
        if(!$id){$id = QMAuth::getUserId();}
        return $id;
    }
    /**
     * @return int[]
     */
    public static function getTestUserIds(): array{
        return UserIdProperty::getTestUserIds();
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return parent::getDetailsField($resolveCallback, $name);
	}
    /**
     * @throws UnauthorizedException
     */
    public function authorizeUpdate(): void {
        throw new UnauthorizedException();
    }
}
