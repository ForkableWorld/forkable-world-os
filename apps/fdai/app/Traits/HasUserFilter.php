<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\User;
use App\Astral\Filters\PeopleFilter;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Http\Requests\AstralRequest;
use App\Http\Requests\ResourceIndexRequest;
trait HasUserFilter {
	use HasFilter;
	public function shouldShowFilter(): bool{
		$user = QMAuth::getQMUser();
		if(!$user->canSeeOtherUsers()){
			return false;
		}
		if(QMRequest::hasReferrerRelationIdFilter()){
			return false;
		}
		$viaRelationship = AstralRequest::isViaRelationship();
		if(AstralRequest::req() instanceof ResourceIndexRequest){
			$relationship = AstralRequest::req()->relationshipType;
			return !AstralRequest::req()->viaRelationship();  // Already filtered by relationship
		}
		return AstralRequest::shouldShowAnyFilters();
	}
	public function getFilterOptions(): array{
		return [
			'Yours' => PeopleFilter::YOURS,
			'Everyone' => PeopleFilter::EVERYONE,
		];
	}
	/**
	 * @param $query
	 * @param $type
	 * @return mixed
	 */
	public function applyFilter($query, $type){
		if($userId = UserIdProperty::fromReferrer()){
			$u = User::findInMemoryOrDB($userId);
			if($u->canReadMe()){
				$query->where(static::NAME, $userId);
				return $query;
			}
		}
		if($type === PeopleFilter::YOURS){
			$query->where(static::NAME, QMAuth::id());
		}
		return $query;
	}
	/**
	 * Set the default options for the filter.
	 * @return array|mixed
	 */
	public function defaultFilter(): string{
		return PeopleFilter::YOURS;
	}
}
