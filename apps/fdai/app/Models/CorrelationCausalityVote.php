<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\Models\Vote;
use App\Models\Base\BaseCorrelationCausalityVote;
use App\Studies\StudyLinks;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasModel\HasGlobalVariableRelationship;
use App\Traits\HasModel\HasUser;
use App\Traits\ModelTraits\IsVote;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\CorrelationCausalityVote
 * @property int $id
 * @property int $cause_variable_id
 * @property int $effect_variable_id
 * @property int|null $correlation_id
 * @property int|null $global_variable_relationship_id
 * @property int $user_id
 * @property int $vote The opinion of the data owner on whether or not there is a plausible
 *                                 mechanism of action by which the predictor variable could influence the outcome
 *     variable.
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
 * @method static Builder|CorrelationCausalityVote newModelQuery()
 * @method static Builder|CorrelationCausalityVote newQuery()
 * @method static Builder|CorrelationCausalityVote query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CorrelationCausalityVote
 *     whereGlobalVariableRelationshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CorrelationCausalityVote
 *     whereCauseVariableId($value)
 * @method static Builder|CorrelationCausalityVote whereClientId($value)
 * @method static Builder|CorrelationCausalityVote whereCorrelationId($value)
 * @method static Builder|CorrelationCausalityVote whereCreatedAt($value)
 * @method static Builder|CorrelationCausalityVote whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CorrelationCausalityVote
 *     whereEffectVariableId($value)
 * @method static Builder|CorrelationCausalityVote whereId($value)
 * @method static Builder|CorrelationCausalityVote whereUpdatedAt($value)
 * @method static Builder|CorrelationCausalityVote whereUserId($value)
 * @method static Builder|CorrelationCausalityVote whereVote($value)
 * @mixin \Eloquent
 * @property bool|null $is_public
 * @method static Builder|CorrelationCausalityVote whereIsPublic($value)
 * @property-read OAClient|null $client
 */
class CorrelationCausalityVote extends BaseCorrelationCausalityVote {
	use Compoships, HasUser, IsVote, HasGlobalVariableRelationship;
	public const CLASS_DESCRIPTION = "User vote indicating whether or not there is a plausible mechanism by which a given factor could influence a given outcome. ";
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
