<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\BaseModel;
use App\Astral\BaseAstralAstralResource;
use App\Traits\AnalyzableTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
class AnalyzeAction extends QMAction implements ShouldQueue {
	use InteractsWithQueue, Queueable;
	public $withoutConfirmation = true;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		// https://github.com/laravel/astral-issues/issues/736
		$this->connection = config('queue.default');
		$this->confirmButtonText = "Analyze it, damnit!";
		/** @var BaseAstralAstralResource $resource */
		$resource = $request->resource();
		$label = $resource::label();
		$this->confirmText =
			"You can see your analyzed $label by clicking Lens => Analyses. Would you like to analyze?";
		$this->name = "Analyze $label";
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
			try {
				$model->analyze(__METHOD__);
			} catch (AlreadyAnalyzedException | AlreadyAnalyzingException | DuplicateFailedAnalysisException | NotEnoughDataException | TooSlowToAnalyzeException $e) {
				$this->markAsFailed($model, $e);
			}
			$this->markAsFinished($model);
		}
		$one = $models->first();
		return $this->respond('Analyzed ' . count($models) . " " . $one->getClassTitlePlural());
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
