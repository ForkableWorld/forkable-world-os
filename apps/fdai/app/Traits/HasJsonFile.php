<?php
namespace App\Traits;
use App\Files\Json\JsonFile;
use App\Models\BaseModel;
use App\Models\Unit;
use Illuminate\Support\Str;
trait HasJsonFile
{
    use HasSeed;
    protected static array $fromJsonCache = [];
    public static function getFromJson(): \Illuminate\Support\Collection{
        $table = static::getTableName();
        if(isset(static::$fromJsonCache[$table])){
            return static::$fromJsonCache[$table];
        }
        $path = static::getJsonFilePath();
        $arr = JsonFile::getArray($path);
        $models = [];
        foreach ($arr as $item){
            if(!is_array($item)){
                le("Item is not an array: " . json_encode($item));
            }
            /** @var BaseModel $me */
            $me = new static;
            $me->forceFill($item);
            $models[$me->getTitleAttribute()] = $me;
        }
        return static::$fromJsonCache[$table] = collect($models);
    }
    /**
     * @return float|int
     */
    protected static function getSeedPath(): string{
        return static::getJsonFilePath();
    }
    /**
     * @return string
     */
    public static function getJsonFilePath(): string
    {
        $tableName = static::getTableName();
        return abs_path('data/' . $tableName . ".json");
    }
	public static function saveJson(): void{
		$all = static::all();
		$byName = [];
		foreach($all as $item){
			if(!$item->slug){
				$item->slug = Str::slug($item->name);
			}
			$arr = $item->toNonNullArray();
			$byName[$item->getTitleAttribute()] = $arr;
		}
		$path = static::getJsonFilePath();
		JsonFile::saveArray($path, $byName);
	}
}
