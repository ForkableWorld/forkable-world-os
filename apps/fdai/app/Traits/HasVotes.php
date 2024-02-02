<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\Vote;
use Awobaz\Compoships\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
trait HasVotes {
	/**
	 * @param int $value
	 * @return Builder
	 */
	public static function whereVoteValue(int $value): Builder{
		$qb = static::whereHas('votes', function($query) use ($value){
			/** @var Builder $query */
			return $query->where('value', '=', $value);
		});
		return $qb;
	}
	public static function whereUpVoted(): Builder{
		return static::whereVoteValue(1);
	}
	/**
	 * @return static[]|Collection
	 */
	public static function getUpVoted(){
		return static::whereUpVoted()->with([
			'cause_variable',
			'effect_variable',
		])->get();
	}
	public function upVote(int $userId){
		$this->votes()->updateOrCreate([Vote::FIELD_USER_ID => $userId], [Vote::FIELD_VALUE => 1]);
	}
}
