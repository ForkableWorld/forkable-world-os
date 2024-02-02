<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\Models\Base\BaseNotification;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Types\QMStr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\Notification
 * @property string $id
 * @property string $type
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property string $data
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Notification newModelQuery()
 * @method static Builder|Notification newQuery()
 * @method static Builder|Notification query()
 * @method static Builder|Notification whereCreatedAt($value)
 * @method static Builder|Notification whereData($value)
 * @method static Builder|Notification whereId($value)
 * @method static Builder|Notification whereNotifiableId($value)
 * @method static Builder|Notification whereNotifiableType($value)
 * @method static Builder|Notification whereReadAt($value)
 * @method static Builder|Notification whereType($value)
 * @method static Builder|Notification whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property Carbon|null $deleted_at
 * @method static Builder|Notification whereDeletedAt($value)
 * @property-read OAClient $client
 * @property-read OAClient $oa_client
 */
class Notification extends BaseNotification {
    use HasFactory;

	public const CLASS_DESCRIPTION = "Tracking reminder notifications, messages, and study results. ";
	const CLASS_CATEGORY = "Miscellaneous";
	public function getTitleAttribute(): string{
		if(!$this->hasId()){return static::getClassNameTitle();}
		$data = $this->getData();
		if(isset($data['title'])){
			$title = $data['title'];
		} else{
			$title = QMStr::afterLast($this->type, '\\') . " " . QMStr::afterLast($this->notifiable_type, '\\') . " " .
				$this->notifiable_id;
		}
		return $title;
	}
	public function getBody(): string{
		$data = $this->getData();
		if(isset($data['body'])){
			$title = $data['body'];
		} else{
			//$title = \App\Logging\QMLog::print_r($this->getData(), true);
			$title = $this->data;
		}
		return $title;
	}
	public function getData(): array{
		return json_decode($this->attributes['data'], true);
	}
	/**
	 * @param \Illuminate\Database\Query\Builder|Builder $qb
	 * @param User|QMUser $user
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function restrictQueryBasedOnPermissions($qb, $user = null): \Illuminate\Database\Query\Builder{
		if(!$user){
			$user = QMAuth::getQMUser();
		}
		if(!$user){
			throw new UnauthorizedException("User not logged in");
		}
		$qb->where(static::TABLE . '.' . static::FIELD_NOTIFIABLE_ID, $user->getId());
		return $qb;
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		$notifiable = $this->getNotifiable();
		return [$notifiable->getButton()];
	}
	/**
	 * @return BaseModel
	 */
	private function getNotifiable(): BaseModel {
		/** @var BaseModel $class */
		$class = $this->notifiable_type;
		return $class::findInMemoryOrDB($this->notifiable_id);
	}
}
