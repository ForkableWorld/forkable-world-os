<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Types;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
class PhpTypes {
	const ARRAY   = 'array';
	const BOOL    = 'bool';
	const BOOLEAN = 'boolean';
	const DATE    = 'date';
	const DOUBLE  = 'double';
	const FLOAT   = 'float';
	const INT     = 'int';
	const INTEGER = 'integer';
	const OBJECT  = 'object';
	const STRING  = 'string';
	/**
	 * @var array
	 */
	public static $phpTypeToPossibleMySQLTypes = [
		self::STRING => [Types::STRING, 'text', self::STRING, 'char', 'enum', 'tinytext', 'mediumtext', 'longtext'],
		self::DATE => [Types::DATETIME_MUTABLE, 'datetime', 'year', self::DATE, 'time'],
		self::INT => [Types::INTEGER, 'bigint', 'integer', 'smallint', 'mediumint'],
		self::INTEGER => [Types::INTEGER, 'bigint', 'integer', 'smallint', 'mediumint'],
		self::FLOAT => [Types::FLOAT, 'decimal', 'numeric', 'dec', 'fixed', 'double', 'real', 'double precision'],
		self::DOUBLE => [Types::FLOAT],
		self::BOOL => [Types::BOOLEAN, 'longblob', 'blob', 'bit'],
		self::BOOLEAN => [Types::BOOLEAN, 'longblob', 'blob', 'bit'],
		self::ARRAY => [Types::ARRAY],
		self::OBJECT => [Types::OBJECT],
	];
	public static function phpTypeToPossibleMySQLTypes(string $phpType): array{
		if(!isset(self::$phpTypeToPossibleMySQLTypes[$phpType])){
			le("please define DB type for PHP type $phpType");
		}
		return self::$phpTypeToPossibleMySQLTypes[$phpType];
	}
	/**
	 * @param $phpType
	 * @return Type
	 */
	public static function phpTypeToMostLikelyMySQLType($phpType): Type{
		$types = self::phpTypeToPossibleMySQLTypes($phpType);
		return MySQLTypes::getType($types[0]);
	}
	public static function mySQLTypeToPHPType(string $mysqlType): string{
		foreach(self::$phpTypeToPossibleMySQLTypes as $phpType => $mySQLTypes){
			if(in_array($mysqlType, $mySQLTypes)){
				return $phpType;
			}
		}
		le("please define PHP for db type $mysqlType");
		throw new \LogicException();
	}
}
