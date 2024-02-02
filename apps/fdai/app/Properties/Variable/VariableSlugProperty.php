<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\Base\BaseSlugProperty;
use App\Traits\PropertyTraits\VariableProperty;
use App\Types\QMStr;
class VariableSlugProperty extends BaseSlugProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public static function fixNulls(): void {
        //Writable::statementStatic("update variables set slug = null where slug is not null;");
        $names = static::whereNull()
            ->orderByDesc(Variable::FIELD_NUMBER_OF_USER_VARIABLES)
            ->pluck(Variable::FIELD_NAME);
        $total = $names->count();
        $i = 0;
        $ids = $failures = [];
        foreach($names as $name){
            $i++;
            $slug = QMStr::slugify($name);
            if($i%100 === 0){
                QMLog::info("Setting slug $slug for $name ($i of $total)...");
            }
            $v = Variable::findByName($name);
            try {
                $v->update([static::NAME => $slug]);
            } catch (\Throwable $e){
                $ids[] = $v->getId();
                $failures[] = [
                    'name' => $name,
                    'url' => $v->getUrl(),
                ];
                break;
            }
        }
        if($ids){
            $url = Variable::getDataLabIndexUrl(['ids' => implode(",", $ids)]);
            QMLog::info("Could not create slug for these variables: $url", $failures);
        }
    }
}
