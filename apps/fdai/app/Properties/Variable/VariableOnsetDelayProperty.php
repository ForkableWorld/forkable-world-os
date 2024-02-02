<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\InvalidStringAttributeException;
use App\Exceptions\InvalidStringException;
use App\Exceptions\RedundantVariableParameterException;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseOnsetDelayProperty;
class VariableOnsetDelayProperty extends BaseOnsetDelayProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @return void
     * @throws InvalidAttributeException
     * @throws RedundantVariableParameterException
     */
    public function validate(): void {
        try {
            parent::validate();
        } catch (InvalidStringAttributeException|InvalidStringException $e) {
            le($e);
        }
        $this->ensureValueDiffersFromVariableCategory();
    }

}
