<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Astral\BaseAstralAstralResource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
class CorrelateAction extends QMAction implements ShouldQueue {
	use InteractsWithQueue, Queueable;
	public $withoutConfirmation = true;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		// https://github.com/laravel/astral-issues/issues/736
		$this->connection = config('queue.default');
		$this->confirmButtonText = "Correlate, damnit!";
		/** @var BaseAstralAstralResource $resource */
		$resource = $request->resource();
		$label = $resource::label();
		$this->confirmText =
			"You can see your user_variable_relationships by clicking VariableRelationships in the menu. Would you like to correlate?";
		$this->name = "Correlate $label";
	}
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param UserVariable[]|Variable[]|Collection $variables
	 * @return array
	 */
	public function handle(ActionFields $fields, Collection $variables): array{
		foreach($variables as $v){
			try {
				$correlated[$v->getNameAttribute()] = $v->correlate();
				$this->markAsFinished($v);
			} catch (\Throwable $e) {
				$this->markAsFailed($v, $e);
			}
		}
		return $this->respond('Correlated ' . count($variables) . " variables!");
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
