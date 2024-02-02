<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Actions;
use App\Exceptions\ModelValidationException;
use App\Exceptions\UnauthorizedException;
use App\Models\BaseModel;
use App\Properties\BaseProperty;
use App\Types\BoolHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Fields\ActionFields;
use App\Http\Requests\AstralRequest;
abstract class ChangeAttributeAction extends QMAction {
	/**
	 * @param AstralRequest|Request $request
	 */
	public function __construct(Request $request){
		$prop = $this->getProperty();
		$this->confirmButtonText = "Change {$prop->getTitleAttribute()}, Damnit!";
		$this->confirmText = $prop->getSubtitleAttribute();
		$this->name = "Set {$prop->getTitleAttribute()}";
	}

    /**
     * Perform the action on the given models.
     * @param ActionFields $fields
     * @param Collection $models
     * @return array
     * @throws UnauthorizedException
     */
	public function handle(ActionFields $fields, Collection $models){
		$prop = $this->getProperty();
		$value = $fields->get($prop->name);
		$dbVal = $prop->toDBValue($value);
		$names = [];
		/** @var BaseModel $model */
		foreach($models as $model){
			$model->authorizePropertyUpdates();
			$model->setAttribute($prop->name, $dbVal);
			try {
				$model->save();
			} catch (ModelValidationException $e) {
				le($e);
			}
			$names[] = $model->getNameAttribute();
		}
		return $this->respond("Set Is Public to " . BoolHelper::toString($value) . " on:\n " . implode(", ", $names));
	}
	/**
	 * Get the fields available on the action.
	 * @return array
	 */
	public function fields(): array{
		return [
			$this->getProperty()->getUpdateField(),
		];
	}
	abstract public function getProperty(): BaseProperty;
}
