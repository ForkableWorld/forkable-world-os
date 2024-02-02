<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models\Clinical;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\Clinical\Intervention
 * @property int $id
 * @property string $nct_id
 * @property string $intervention_type
 * @property string $name
 * @property string $description
 * @method static Builder|Intervention newModelQuery()
 * @method static Builder|Intervention newQuery()
 * @method static Builder|Intervention query()
 * @method static Builder|Intervention whereDescription($value)
 * @method static Builder|Intervention whereId($value)
 * @method static Builder|Intervention whereInterventionType($value)
 * @method static Builder|Intervention whereName($value)
 * @method static Builder|Intervention whereNctId($value)
 * @mixin \Eloquent
 */
class Intervention extends Model {
	/**
	 * Indicates if the model should be timestamped.
	 * @var bool
	 */
	public $timestamps = false;
	/**
	 * The connection name for the model.
	 * @var string
	 */
	protected $connection = 'clinical_trials';
}
