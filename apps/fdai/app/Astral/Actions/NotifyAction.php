<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Models\BaseModel;
use App\Traits\HardCodable;
use App\Traits\HasDBModel;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
class NotifyAction extends QMAction {
	public $withoutConfirmation = true;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		$this->confirmButtonText = "Notify";
		$this->confirmText = "Send notification";
		$this->name = "Notify";
	}
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection $models
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $models){
		$names = [];
		/** @var HasDBModel|BaseModel $model */
		foreach($models as $model){
			/** @var HardCodable $dbm */
			$model->notify();
			$names[] = $model->getNameAttribute();
			$this->markAsFinished($model);
		}
		return $this->success("Notified " . implode(",", $names));
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
