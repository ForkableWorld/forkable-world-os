<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Models\BaseModel;
use App\Slim\Middleware\QMAuth;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
use Overtrue\LaravelLike\Traits\Likeable;
class FollowAction extends QMAction {
	use InteractsWithQueue, Queueable;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		$this->confirmButtonText = "Send Request";
		$this->confirmText =
			"If your friend accepts your request, you'll be able to see their data by clicking their profile in the Users page. ";
		$this->name = "Send Follow Request?";
	}
	/**
	 * Perform the action on the given models.
	 * @param ActionFields $fields
	 * @param Collection $models
	 * @return mixed
	 */
	public function handle(ActionFields $fields, Collection $models){
		$user = QMAuth::getQMUser()->l();
		$message = "";
		foreach($models as $model){
			/** @var Likeable|BaseModel $model */
			$user->follow($model);
			$message .= "Followed " . $model->getTitleAttribute() . "\n";
		}
		return $this->respond($message);
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [];
	}
}
