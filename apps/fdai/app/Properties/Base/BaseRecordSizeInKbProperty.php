<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\Logging\QMLog;
use App\Models\BaseModel;
use App\Models\Variable;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
use App\Slim\Middleware\QMAuth;
class BaseRecordSizeInKbProperty extends BaseProperty{
	use IsInt;
    const LARGEST_FIELD = Variable::FIELD_CHARTS;
    public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'record_size_in_kb';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::RECORD_VINYL_SOLID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::BUSINESS_COLLECTION_RECORD;
	public $isOrderable = false;
	public $maximum = 100;
	public $minimum = 1;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'record_size_in_kb';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Record Size in Kb';
	public $type = self::TYPE_INTEGER;
    /**
     * @return BaseModel[]
     */
    public static function updateAll(): array{
        return static::handleNulls();
    }
    public static function handleNulls(): array{
        $table = static::getTable();
        $largestField = static::LARGEST_FIELD;
        $sql = "update $table set record_size_in_kb = length($largestField)/1024
            where record_size_in_kb is null and
                  $largestField is not null
            limit 10000;";
        while($count = db_statement($sql)){
            $qb = static::whereNullQMQB()
                ->whereNotNull($largestField);
            $count = $qb->count();
            \App\Logging\ConsoleLog::info("$count still null");
            if(!$count){
                break;
            }
        }
        return [];
    }
    /**
     * @param int $id
     * @return BaseModel
     */
    protected static function handleNull(int $id): BaseModel {
        $m = static::findParent($id);
        $size = round(strlen(json_encode($m->toArray()))/1024);
        $m->record_size_in_kb = $size;
        $m->logInfo("$size kb");
        $m->forceSave();
        return $m;
    }
    public function showOnCreate(): bool{return false;}
    public function showOnUpdate(): bool{return false;}
    public function showOnDetail(): bool{return QMAuth::isAdmin();}
    public function showOnIndex(): bool{return false;}
}
