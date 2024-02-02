<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Properties\TrackingReminder\TrackingReminderReminderStartTimeProperty;
use App\Slim\Middleware\QMAuth;
use App\Types\TimeHelper;
use Laraning\AstralTimeField\TimeField;
use App\Fields\Field;
trait IsTime {
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
		return $this->getTextField($name ?? $this->getTitleAttribute(),
			$resolveCallback ?? function($value, $resource, $attribute){
				return TimeHelper::humanTimeOfDay($value, QMAuth::getQMUser() ?? $this->getUser());
			})->updateLink();
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
		return $this->getTimeField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
		return $this->getTimeField($name, $resolveCallback);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
		return $this->getTimeField($name, $resolveCallback);
	}
	public function getExample(): string{ return TrackingReminderReminderStartTimeProperty::DEFAULT_LOCAL_REMINDER_TIME; }
	/**
	 * @param string|null $title
	 * @param $resolveCallback
	 * @return TimeField
	 */
	protected function getTimeField(?string $title, $resolveCallback): TimeField{
		$f = (new TimeField($title ?? $this->getTitleAttribute(), $this->name, $resolveCallback))->withTwelveHourTime();
		return $f;
	}
}
