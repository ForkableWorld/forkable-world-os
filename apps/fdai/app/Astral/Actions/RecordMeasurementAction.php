<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Models\Measurement;
use App\Models\Variable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Fields\Number;
class RecordMeasurementAction extends QMAction {
	use InteractsWithQueue, Queueable;
	public $withoutConfirmation = true;
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection $models
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $models){
		/** @var Variable $variable */
		foreach($models as $variable){
			$m = new Measurement();
			$m->unit_id = $variable->default_unit_id;
			foreach($fields as $field){
				$attr = $field->name;
				$val = $field->value;
				$m->$attr = $val;
			}
		}
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [
			Number::make("Value")->help("The value you want to record")->rules("required|numeric"),
		];
	}
}
