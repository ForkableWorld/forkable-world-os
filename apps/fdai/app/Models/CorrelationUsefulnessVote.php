<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Vote;
use App\Models\Base\BaseCorrelationUsefulnessVote;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasModel\HasUser;
use App\Traits\ModelTraits\IsVote;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\CorrelationUsefulnessVote
 * @property int $id
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property int|null $correlation_id
 * @property int|null $global_variable_relationship_id
 * @property int $user_id
 * @property int $vote The opinion of the data owner on whether or not knowledge of this
 *                     relationship is useful in helping them improve an outcome of interest.
 *                     -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
 *                     previous vote.  null corresponds to never having voted before.
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $client_id
 * @property-read GlobalVariableRelationship|null $global_variable_relationship
 * @property-read OAClient|null $oa_client
 * @property-read Variable $cause_variable
 * @property-read UserVariableRelationship|null $correlation
 * @property-read Variable $effect_variable
 * @property-read User $user
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|CorrelationUsefulnessVote newModelQuery()
 * @method static Builder|CorrelationUsefulnessVote newQuery()
 * @method static Builder|CorrelationUsefulnessVote query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CorrelationUsefulnessVote
 *     whereGlobalVariableRelationshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CorrelationUsefulnessVote
 *     whereCauseVariableId($value)
 * @method static Builder|CorrelationUsefulnessVote whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CorrelationUsefulnessVote
 *     whereCorrelationId($value)
 * @method static Builder|CorrelationUsefulnessVote whereCreatedAt($value)
 * @method static Builder|CorrelationUsefulnessVote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CorrelationUsefulnessVote
 *     whereEffectVariableId($value)
 * @method static Builder|CorrelationUsefulnessVote whereId($value)
 * @method static Builder|CorrelationUsefulnessVote whereUpdatedAt($value)
 * @method static Builder|CorrelationUsefulnessVote whereUserId($value)
 * @method static Builder|CorrelationUsefulnessVote whereVote($value)
 * @mixin \Eloquent
 * @property bool|null $is_public
 * @method static Builder|CorrelationUsefulnessVote whereIsPublic($value)
 * @property-read OAClient|null $client
 */
class CorrelationUsefulnessVote extends BaseCorrelationUsefulnessVote {
	use HasUser, Compoships, IsVote;
	public const CLASS_DESCRIPTION = 'The opinion of the data owner on whether or not knowledge of this
                relationship is useful in helping them improve an outcome of interest.
                -1 corresponds to a down vote. 1 corresponds to an up vote. 0 corresponds to removal of a
                previous vote.  null corresponds to never having voted before.';
	public const CLASS_CATEGORY = Study::CLASS_CATEGORY;

	public function upVoted(): bool{
		if($this->vote === null){
			return false;
		}
		return $this->vote === \App\Models\Vote::UP_VALUE;
	}
	public function downVoted(): bool{
		if($this->vote === null){
			return false;
		}
		return $this->vote === \App\Models\Vote::DOWN_VALUE;
	}
}
