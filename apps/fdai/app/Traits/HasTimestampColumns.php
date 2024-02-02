<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\InvalidTimestampException;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Types\TimeHelper;
use Illuminate\Database\Eloquent\Builder;
trait HasTimestampColumns {
	public function getTimeSinceUpdated(): string{
		return TimeHelper::timeSinceHumanString($this->getAttribute(BaseModel::UPDATED_AT));
	}
	public function getTimeSinceCreated(): string{
		return TimeHelper::timeSinceHumanString($this->getAttribute(BaseModel::CREATED_AT));
	}
	public function getTimeSinceDeleted(): string{
		return TimeHelper::timeSinceHumanString($this->getAttribute(BaseModel::FIELD_DELETED_AT));
	}
	/**
	 * @return string
	 */
	public function getTimeSinceCreatedAt(): string{
		return TimeHelper::timeSinceHumanString($this->getCreatedAt());
	}
	/**
	 * @param $seconds
	 * @return bool
	 */
	public function createdMoreThanXSecondsAgo($seconds): bool{
		try {
			return TimeHelper::universalConversionToUnixTimestamp($this->createdAt) < time() - $seconds;
		} catch (InvalidTimestampException $e) {
			le($e);
		}
	}
	/**
	 * @param int $limit
	 */
	public static function logRecent(int $limit = 100){
		QMLog::table(static::whereRecent($limit)->get(),
			"=== Most Recently Updated $limit " . static::getPluralizedClassName() . " ===");
	}
	/**
	 * @param int $limit
	 * @return Builder
	 */
	public static function whereRecent(int $limit = 100): Builder{
		return static::query()->orderBy(static::TABLE . '.' . static::FIELD_UPDATED_AT, self::ORDER_DIRECTION_DESC)
			->limit($limit);
	}
	/**
	 * @return string
	 */
	public function getCreatedAt(): ?string{
		return $this->attributes[BaseModel::CREATED_AT] ?? null;
	}
	/**
	 * @return string
	 */
	public function timeSinceCreatedAtHumanString(): string{
		return TimeHelper::timeSinceHumanString($this->createdAt);
	}
}
