<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Files\PHP\PhpClassFile;
use App\Models\BaseModel;
use App\Models\Connector;
use App\Models\Unit;
use App\Models\Variable;
use App\Models\VariableCategory;
use App\Slim\Model\StaticModel;
use App\Types\QMStr;
use Illuminate\Support\Str;
use Nette\PhpGenerator\ClassType;
trait HasClassName {
	use HasConstants;
	/**
	 * @param bool $snakize
	 * @return string
	 */
	public static function getShortClassName(bool $snakize = false): string{
		$class = QMStr::toShortClassName(static::class);
		if($snakize){
			return QMStr::snakize($class);
		}
		return $class;
	}
	/**
	 * @param bool $snakize
	 * @return string
	 */
	public static function getFullClassName(bool $snakize = false): string{
		if($snakize){
			return QMStr::snakize(static::class);
		}
		return static::class;
	}
	public static function getClassNameTitle(): string{
		$str = QMStr::classToTitle((new \ReflectionClass(static::class))->getShortName());
		$str = QMStr::stripPrefixes($str);
		return $str;
	}
	public static function getClassNameTitlePlural(): string{
		return QMStr::pluralize(self::getClassNameTitle());
	}
	public static function getNamespace(): string{
		return str_replace('\\' . self::getShortClassName(), "", self::getFullClassName());
	}
	public static function getFolder(): string{
		return FileHelper::namespaceToFolder(static::getNameSpace());
	}
	public static function getClassTitlePlural(): string{
		/** @var BaseModel $class */
		$class = static::class;
		if(defined("$class::TABLE")){
			return QMStr::tableToTitle($class::TABLE);
		}
		return static::getClassNameTitlePlural();
	}
	/**
	 * @return string
	 */
	public static function getSlugifiedClassName(): string{
		$slug = QMStr::slugify(static::getShortClassName(true));
		$slug = str_replace('qm-', '', $slug);
		$slug = str_replace('q-m-', '', $slug);
		return $slug;
	}
	/**
	 * @return string
	 */
	public static function getSlugifiedTableName(): string{
		$slug = QMStr::slugify(static::getClassNameTitlePlural());
		return $slug;
	}
	public function getPHPCode(): string{
		try {
			return FileHelper::getContents($this->getModelFilePath());
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
	}
	public static function addTrait(string $traitClass){
		FileHelper::addTrait($traitClass, static::getModelFilePath());
	}
	public static function getModelFilePath(): string{
		return FileHelper::classToPath(static::class);
	}
	/**
	 * Get the table associated with the model.
	 * @return string
	 */
	public static function getPluralizedSlugifiedClassName(): string{
		/** @var StaticModel $className */
		$className = static::class;
		if(defined($className . '::TABLE')){
			$table = $className::TABLE;
			if(in_array($table, [
				Variable::TABLE,
				Unit::TABLE,
				Connector::TABLE,
				VariableCategory::TABLE,
			])){
				return str_replace("_", "-", $table);
			}
		}
		$basename = class_basename($className);
		$name = str_replace('\\', '', Str::snake(Str::plural($basename)));
		$slug = str_replace('_', '-', $name);
		$slug = str_replace('q-m-', '', $slug);
		return $slug;
	}
	public function getClassType(): PhpClassFile{
		return PhpClassFile::find(static::class);
	}
	/**
	 * @param string $key
	 * @param string $access
	 * @param        $value
	 */
	private function addProperty(string $key, string $access = ClassType::VISIBILITY_PUBLIC, $value = null){
		$file = $this->getClassType();
		$file->addProperty($key, $access, $value);
	}
}
