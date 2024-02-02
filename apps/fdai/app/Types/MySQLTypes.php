<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
use App\CodeGenerators\JSONtoMYSQL\DatabaseException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\FileHelper;
use App\Traits\HasConstants;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use MarkTopper\DoctrineDBALTimestampType\TimestampType;

class MySQLTypes {
	use HasConstants;
	const VARCHAR = 'varchar';
	const TEXT = 'text';
	const STRING = 'string';
	const CHAR = 'char';
	const ENUM = 'enum';
	const TINYTEXT = 'tinytext';
	const MEDIUMTEXT = 'mediumtext';
	const LONGTEXT = 'longtext';
	const DATETIME = 'datetime';
	const YEAR = 'year';
	const DATE = 'date';
	const TIME = 'time';
	const JSON = 'json';
	const TIMESTAMP = 'timestamp';
	const BIGINT = 'bigint';
	const INT = 'int';
	const INTEGER = 'integer';
	const TINYINT = 'tinyint';
	const SMALLINT = 'smallint';
	const MEDIUMINT = 'mediumint';
	const FLOAT = 'float';
	const DECIMAL = 'decimal';
	const NUMERIC = 'numeric';
	const DEC = 'dec';
	const FIXED = 'fixed';
	const DOUBLE = 'double';
	const REAL = 'real';
	const DOUBLE_PRECISION = 'double precision';
	const LONGBLOB = 'longblob';
	const BLOB = 'blob';
	const BIT = 'bit';
	/**
	 * will determine a valid mysql column type from
	 * the input variable value
	 * @param mixed $val
	 * @return string
	 * @throws DatabaseException
	 */
	public static function typeNameForValue($val): ?string{
		if(is_object($val) || is_array($val)){
			return Types::TEXT;
		} elseif(preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $val)){
			return Types::DATETIME_MUTABLE;
		} elseif(preg_match('/\d{4}-\d{2}-\d{2}/', $val)){
			return Types::DATE_MUTABLE;
		} elseif(is_string($val) && strlen($val) < 255){
			return Types::STRING;
		} elseif(is_string($val)){
			return Types::TEXT;
		} elseif(is_bool($val)){
			return Types::BOOLEAN;
		} elseif(is_int($val)){
			return Types::BIGINT;
		} elseif(is_object($val) || is_array($val)){
			return Types::TEXT;
		} elseif(is_double($val) || is_float($val) || is_real($val)){
			return Types::FLOAT;
		} elseif(!is_null($val)){
			throw new DatabaseException("unknown mysql type for: " . gettype($val));
		}
		return null;
	}
	/**
	 * @param $value
	 * @return Type
	 * @throws DatabaseException
	 */
	public static function typeFromValue($value): Type{
		$typeName = self::typeNameForValue($value);
		return self::getType($typeName);
	}
	/**
	 * @param string $phpType
	 * @return Type
	 */
	public static function phpTypeToMostLikelyMySQLType(string $phpType): Type{
		return PhpTypes::phpTypeToMostLikelyMySQLType($phpType);
	}
	public static function phpTypeToPossibleMySQLTypes(string $phpType): array{
		return PhpTypes::phpTypeToPossibleMySQLTypes($phpType);
	}
	public static function generateTraits(){
		foreach(self::getConstants() as $mysqlType){
			$phpType = self::mySQLTypeToPHPType($mysqlType);
			$stub = self::getStub();
			$mysqlTypeTrait = "IsMySql" . QMStr::toShortClassName($mysqlType);
			$stub = str_replace("{{mysql_type_trait}}", $mysqlTypeTrait, $stub);
			$stub = str_replace("{{php_type_trait}}", "Is" . QMStr::toShortClassName($phpType), $stub);
			FileHelper::writeByFilePath("app/Traits/PropertyTraits/MySqlTypeTraits/$mysqlTypeTrait.php", $stub);
		}
	}
	public static function mySQLTypeToPHPType(string $mysqlType): string{
		return PhpTypes::mySQLTypeToPHPType($mysqlType);
	}
	protected static function getStub(): string{
		try {
			$str = FileHelper::getContents("app/Traits/PropertyTraits/MySqlTypeTraits/MySqlTypeTrait.stub");
		} catch (QMFileNotFoundException $e) {
			le($e);
		}
		return $str;
	}
	/**
	 * Factory method to create type instances.
	 * Type instances are implemented as flyweights.
	 * @param string $name The name of the type (as returned by getName()).
	 * @return Type
	 */
	public static function getType(string $name): Type{
		try {
			if($name === "varchar"){
				$name = Types::STRING;
			}
            if($name === "timestamp"){
                return new TimestampType();
            }
			return Type::getType($name);
		} catch (Exception | DBALException $e) {
			le($e);
		}
	}
}
