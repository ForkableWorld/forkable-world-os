<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Properties\Base\BaseNumberOfUserVariablesProperty;
use App\Slim\Middleware\QMAuth;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Fields\Field;
class VariableNumberOfUserVariablesProperty extends BaseNumberOfUserVariablesProperty
{
    use VariableProperty;
    use IsNumberOfRelated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param BaseModel|Variable $model
     * @return float
     */
    public static function calculate($model): float{
        $val = count($model->getUserVariables());
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return $this->getHasManyField('user_variables');
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return QMAuth::isAdmin();}
}
