<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Traits\AnalyzableTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Actions\Action;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
class PHPUnitAction extends QMAction {
	use InteractsWithQueue, Queueable;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		$this->withoutConfirmation();
		$this->name = "Download PHPUnit Test";
	}
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection $models
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $models){
		/** @var AnalyzableTrait $m */
		$m = $models->first();
		return Action::redirect($m->getPHPUnitTestUrl());
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
