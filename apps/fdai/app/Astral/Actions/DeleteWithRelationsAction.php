<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Models\BaseModel;
use App\Astral\BaseAstralAstralResource;
use App\Traits\AnalyzableTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
class DeleteWithRelationsAction extends QMAction {
	use InteractsWithQueue, Queueable;
	public $withoutConfirmation = true;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		// https://github.com/laravel/astral-issues/issues/736
		$this->connection = config('queue.default');
		$this->confirmButtonText = "Delete everything, damnit!";
		/** @var BaseAstralAstralResource $resource */
		$resource = $request->resource();
		$label = $resource::label();
		$this->confirmText = "Are you sure you want to hard-delete this $label and all related records, you idiot?";
		$this->name = "Hard-Delete With Relations";
	}
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection $models
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $models){
		/** @var AnalyzableTrait|BaseModel $model */
		foreach($models as $model){
			$model->hardDeleteWithRelations(static::class);
			$this->markAsFinished($model);
		}
		$one = $models->first();
		return $this->respond('Deleted ' . count($models) . " " . $one->getClassTitlePlural());
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
