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
class UnFavoriteAction extends QMAction {
	use InteractsWithQueue, Queueable;
	/**
	 * @param AstralRequest|Request $request
	 * @noinspection SqlResolve
	 * @noinspection SqlWithoutWhere
	 */
	public function __construct(Request $request){
		$this->confirmButtonText = "Delete Favorite, Damnit!";
		/** @var BaseAstralAstralResource $resource */
		$resource = $request->resource();
		$label = $resource::label();
		$this->confirmText =
			"You can see your favorited $label by clicking Lens => Favorites. Would you like to remove this to your favorite list?";
		$this->name = "Delete from Favorites";
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
			/** @var BaseModel $model */
			$user->unfavorite($model);
			$message .= "Un-favorited " . $model->getTitleAttribute() . "\n";
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
