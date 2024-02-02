<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Models\BaseModel;
use App\Astral\BaseAstralAstralResource;
use App\Slim\Middleware\QMAuth;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
use Overtrue\LaravelLike\Traits\Likeable;
class LikeAction extends QMAction {
	use InteractsWithQueue, Queueable;
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		$this->confirmButtonText = "I like it, damnit!";
		/** @var BaseAstralAstralResource $resource */
		$resource = $request->resource();
		$label = $resource::label();
		$this->confirmText = "You can see your liked $label by clicking Lens => Likes.";
		$this->name = "Do you really like it?";
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
			$user->like($model);
			$message .= "Liked " . $model->getTitleAttribute() . "\n";
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
