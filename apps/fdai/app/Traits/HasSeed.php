<?php
namespace App\Traits;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Files\Json\JsonFile;
use App\Logging\ConsoleLog;
use App\Models\BaseModel;
use App\Storage\DB\TestDB;
use App\Storage\Memory;
use App\Types\QMArr;
use Illuminate\Support\Collection;
/**
 * @mixin BaseModel
 */
trait HasSeed
{
    /**
     * @param string|null $filename
     * @param array $arr
     * @return void
     * @throws InvalidAttributeException
     * @throws ModelValidationException
     */
    public static function dumpTestFixture(string $filename = null, array $arr = []): void{
        if($arr) {
            $models = self::fixtureToModels($arr);
        } else {
            /** @var BaseModel[]|Collection $models */
            $models = static::get();
        }
        if(!$models->count()){
            ConsoleLog::info("No fixtures to dump for " . static::getTableName());
            return;
        }
        $arr = self::preProcessDumpModels($models);
        $tableName = static::getTableName();
        if(!$tableName){
            ConsoleLog::info("Table " . $tableName . " does not exist, so no fixtures to dump");
            return;
        }
        self::saveFixture($filename, $arr);
    }
    public static function seed(): void
    {
        $arr = self::getSeedData();
        foreach ($arr as $item){
            /** @var BaseModel $me */
            $me = new static;
            $appends = $me->appends;
            foreach ($item as $key => $value){
                if(in_array($key, $appends)){
                    unset($item[$key]);
                }
            }
            $me->forceFill($item);
            try {
                $me->save();
            } catch (ModelValidationException $e) {
                le($e);
            }
        }
    }
    /**
     * @return string
     */
    protected static function getSeedPath(): string
    {
        return abs_path('tests/fixtures/' . static::getTableName() . ".json");
    }
    /**
     * @param array $arr
     * @return Collection|BaseModel
     */
    private static function fixtureToModels(array $arr): Collection
    {
        $arr = collect($arr);
        $models = [];
        foreach ($arr as $item) {
            $item = QMArr::removeNulls($item);
            $m = new static();
            $m->forceFill($item);
            $models[$m->getTitleAttribute()] = $m;
        }
        $models = collect($models);
        return $models;
    }
    /**
     * @param Collection|BaseModel[] $models
     * @return Collection|BaseModel[]
     * @throws ModelValidationException
     * @throws InvalidAttributeException
     */
    private static function validateModels($models): Collection
    {
        foreach ($models as $m) {
            $m->validate();
            $models[$m->getTitleAttribute()] = $m;
        }
        $models = collect($models);
        return $models;
    }

    /**
     * @param string|null $filename
     * @param array $arr
     * @return void
     */
    private static function saveFixture(?string $filename, array $arr): void
    {
        $testFixturePath = static::getSeedPath();
        JsonFile::write($filename ?? $testFixturePath, $arr);
    }

    /**
     * @param BaseModel[]|Collection $models
     * @return array
     * @throws InvalidAttributeException
     * @throws ModelValidationException
     */
    private static function preProcessDumpModels($models): array
    {
        self::validateModels($models);
        foreach ($models as $model) {
            foreach ($model->getPropertyModels() as $key => $prop) {
                if ($prop->isTemporal() &&
                    $prop->getValue() === null &&
                    $prop->getExample() === null) {
                    $model->setAttribute($key, $prop->getExample());
                }
            }
        }
        $byName = [];
        foreach ($models as $model) {
            $key = $model->getUniqueNamesSlug();
            if(isset($byName[$key])){
                le("Duplicate title: " . $model->getTitleAttribute());
            }
            $byName[$key] = $model;
        }
        $arr = collect($byName)->map(function (BaseModel $one) {
            return $one->getNonNullAttributes();
        })->all();
        $first = $models->first();
        $appends = $first->appends;
        $required = $first->getRequiredFields();
        foreach ($arr as $i => $item) {
            ksort($item);
            foreach ($item as $key => $value) {
                if (in_array($key, $appends)) {
                    unset($item[$i][$key]);
                }
            }
            foreach ($required as $req){
                if(!isset($item[$i][$req])){
                    $item[$i][$req] = $first->getPropertyModel($req)->getExample();
                }
            }
        }
        return $arr;
    }

    /**
     * @return void
     * @throws InvalidAttributeException
     * @throws ModelValidationException
     */
    public static function reprocessSeed(){
        TestDB::migrate();
        Memory::set(Memory::REPROCESSING, true);
        $arr = static::getSeedData();
        static::dumpTestFixture(static::getSeedPath(), $arr);
        Memory::set(Memory::REPROCESSING, false);
    }
	/**
     * @return array
     */
    public static function getSeedData(): array
    {
        $path = static::getSeedPath();
        $arr = JsonFile::getArray($path);
        return $arr;
    }
}
