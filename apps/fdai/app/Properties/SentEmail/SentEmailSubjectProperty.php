<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\SentEmail;
use App\Fields\Field;
use App\Models\SentEmail;
use App\Properties\Base\BaseSubjectProperty;
use App\Traits\PropertyTraits\SentEmailProperty;
use App\Types\QMStr;
class SentEmailSubjectProperty extends BaseSubjectProperty
{
    use SentEmailProperty;
	public const MAX_SUBJECT_LENGTH = 250;
	public $table = SentEmail::TABLE;
    public $parentClass = SentEmail::class;
	public $maxLength = self::MAX_SUBJECT_LENGTH;
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getTextField($name, $resolveCallback)
            ->displayUsing(function($value, $resource, $attribute){
                return QMStr::truncate($value, 40);
            });
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
	public function validate(): void{
		parent::validate();
	}
}
