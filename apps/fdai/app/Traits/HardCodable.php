<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\NotFoundException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Models\Connector;
use App\Slim\Model\DBModel;
use App\Storage\LocalFileCache;
use App\Types\TimeHelper;
use App\VariableCategories\BiomarkersVariableCategory;
use LogicException;
trait HardCodable {
	public function generateChildModelCode(): string{
		$content = $this->generateFileContentOfHardCodedModel();
		$directory = $this->getHardCodedDirectory();
		return FileHelper::writeByDirectoryAndFilename($directory, $this->getHardCodedFileName(), $content);
	}
	public function saveHardCodedModel(): string{
		return $this->generateChildModelCode();
	}
	/**
	 * @return string
	 */
	protected function getHardCodedFileName(): string{
		return $this->getHardCodedShortClassName() . ".php";
	}
	/**
	 * @return string
	 */
	protected function getHardCodedFilePath(): string{
		return $this->getHardCodedDirectory() . "/" . $this->getHardCodedFileName();
	}
	/**
	 * @return string
	 * @throws QMFileNotFoundException
	 */
	protected function getHardCodedFileContents(): string{
		return FileHelper::getContents($this->getHardCodedFilePath());
	}
	abstract protected function generateFileContentOfHardCodedModel(): string;
	abstract protected function getHardCodedShortClassName(): string;
	/**
	 * @return int
	 */
	public static function getLastModifiedConstantFileTime(): int{
		$path = self::getHardCodedDirectory();
		$short = (new \ReflectionClass(static::class))->getShortName();
		$last = FileHelper::getLastModifiedTimeInFolder($path, "/$short.php");
		if(!$last){
			le("$path has no last modified time!");
		}
		return $last;
	}
	/**
	 * @return string
	 */
	abstract public static function getHardCodedDirectory(): string;
	/**
	 * @return array
	 */
	public static function updateDatabaseTableFromHardCodedConstants(): array{
		QMLog::logStartOfProcess(static::TABLE . ' ' . __FUNCTION__);
		/** @var DBModel[] $hardCodedModels */
		$hardCodedModels = static::get();
		$allChanges = [];
		foreach($hardCodedModels as $hardCodedModel){
            if($hardCodedModel instanceof BiomarkersVariableCategory){
                debugger("oura");
            }
			try {
				$l = $hardCodedModel->firstLaravelModel();
			} catch (NotFoundException $e) { // New Unit for instance
				QMLog::info(__METHOD__.": ".$e->getMessage());
				if($hardCodedModel->id && stripos($e->getMessage(), "not get laravel") !== false){
					$l = $hardCodedModel->newLaravelModel();
					if($l instanceof Connector){
						$l->getOrCreateClient();
					}
					try {
						$l->insert($l->attributesToArray());
					} catch (\Throwable $e) {
						$l->insert($l->attributesToArray());
						QMLog::info(__METHOD__.": ".$e->getMessage());
					}
				} else{
					/** @var LogicException $e */
					throw $e;
				}
			}
			if(!$l->id){
				le("!\$l->id");
			}
			foreach($hardCodedModel as $key => $new){
				$columnName = static::getDbFieldNameForProperty($key);
				if(empty($columnName)){
					continue;
				}
                if(!\DB::getSchemaBuilder()->hasColumn($t = static::TABLE, $columnName)){
                    ConsoleLog::info("No DB Column for $t $columnName");
                    continue;
                }
				/** @var HardCodableProperty $prop */
				$prop = $l->getPropertyModel($columnName);
                if(!$prop){
                    $l->generatePropertyModel($columnName);
                    $prop = $l->getPropertyModel($columnName);
                    le("no $columnName");
                }
				$prop->setFromHardCodedValue($new);
			}
			$changes = $l->getChangeList();
			if(!$changes){
				continue;
			}
			$allChanges[$l->getTitleAttribute()] = $changes;
			try {
                $l->exists = $l->find($l->id);
                if($hardCodedModel instanceof BiomarkersVariableCategory){
                    debugger("oura");
                }
				$l->save();
			} catch (\Throwable $e) {
				$message = "Could not update with the below changes because:\n" . $e->getMessage();
				QMLog::error($message, $changes);
				le($e, [
					"message" => $message,
					"changes" => $changes,
					"Model we couldn't save " => $l,
				]);
			}
		}
		$class = (new \ReflectionClass(static::class))->getShortName();
		if(!$allChanges){
			QMLog::info("No hard-coded $class changes...");
		} else{
			QMLog::info("Hard-coded $class changes: " . \App\Logging\QMLog::print_r($allChanges, true));
		}
		LocalFileCache::set(self::getLastConstantImportPath(), time());
		QMLog::logEndOfProcess(static::TABLE . ' ' . __FUNCTION__);
		return $allChanges;
	}
	/**
	 * @return string
	 */
	public static function getLastConstantImportPath(): string{
		return 'tests/fixtures/' . static::TABLE . '-last-imported.json';
	}
	public static function updateDatabaseTableFromHardCodedConstantsIfNecessary(): bool{
		$secondsSinceConstantsLastModified = static::secondsSinceConstantsLastModified();
		$lastImportedTime = static::constantsLastImported();
        $secondsSinceLastImport = time() - $lastImportedTime;
		if($secondsSinceConstantsLastModified < $secondsSinceLastImport){
			\App\Logging\ConsoleLog::info("Need to import " . static::TABLE .
				" constants because lastModified > lastImported");
			static::updateDatabaseTableFromHardCodedConstants();
			return true;
		}
		return false;
	}
	/**
	 * @return int
	 */
	public static function secondsSinceConstantsLastModified(): int{
		$dir = self::getHardCodedDirectory();
		$last = FileHelper::getSecondsSinceLastModified($dir);
		if($last === null){
			le("No getSecondsSinceLastModified from  " . $dir);
		}
		return $last;
	}
	/**
	 * @return int
	 */
	public static function constantsLastImported(): ?int{
		$time = LocalFileCache::get(self::getLastConstantImportPath());
		\App\Logging\ConsoleLog::info("Last imported test DB " . TimeHelper::timeSinceHumanString($time));
		return $time;
	}
    public function generatePropertyModel(string $field){
        static::generateProperties((new static)->getConnectionName(), $field);
    }
}
