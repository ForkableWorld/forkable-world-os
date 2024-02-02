<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Buttons\Admin\PHPStormButton;
use App\Models\BaseModel;
use App\Traits\HardCodable;
use App\Traits\HasDBModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
class GenerateHardCodedModelAction extends QMAction implements ShouldQueue {
	public $withoutConfirmation = true;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		$this->confirmButtonText = "Generate it";
		$this->confirmText = "Save hard coded model";
		$this->name = "Save Hard-Coded Model";
	}
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection $models
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $models){
		/** @var HasDBModel|BaseModel $model */
		foreach($models as $model){
			/** @var HardCodable $dbm */
			$dbm = $model->getDBModel();
			$path = $dbm->saveHardCodedModel();
			$this->markAsFinished($model);
		}
		return $this->redirect(PHPStormButton::redirectUrl($path));
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
