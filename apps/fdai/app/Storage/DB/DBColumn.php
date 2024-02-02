<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Logging\QMLog;
use App\Models\GithubRepository;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\Types\QMStr;
use Doctrine\DBAL\Platforms\MySQL57Platform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DBColumn extends Column {
	public ?DBTable $table = null;
	/**
	 * @param Column|null $column
	 * @param string|null $name
	 * @param Type|null $type
	 */
	public function __construct(Column $column = null, string $name = null, Type $type = null,
                                DBTable $table = null) {
		$this->table = $table;
		if($column){
			foreach($column as $key => $value){
				$this->$key = $value;
			}
		}
		parent::__construct($name??$column->getName(), $type??$column->getType());
	}
	/**
	 * @param string $table
	 * @param string $column
	 * @return DBColumn
	 */
	public static function find(string $table, string $column): ?DBColumn {
		$table = DBTable::find($table);
		try {
			return $table->getDBColumn($column);
		} catch (SchemaException $e) {
			return null;
		}
	}
	/**
	 * @param string $name
	 * @return self[]|Collection
	 */
	public static function fromAllTables(string $name): Collection{
		$tables = DBTable::all();
		foreach($tables as $table){
			if($table->hasColumn($name)){
				try {
					$columns[$table->getName()] = new static($table->getColumn($name), null, null, $table);
				} catch (SchemaException $e) {
				}
			}
		}
		return collect($columns ?? []);
	}
	/**
	 * @param string $name
	 * @param $value
	 * @return DBColumn
	 */
	public static function fromData(string $name, $value): DBColumn{
		$all = DBColumn::fromAllTables($name);
		$phpType = gettype($value);
		if($value === null){
			$phpType = PhpTypes::STRING;
			QMLog::error("Guessing string for null $name");
		}
		$mysqlType = MySQLTypes::phpTypeToMostLikelyMySQLType($phpType);
		if($c = $all->first()){
			foreach($all as $one){
				$c = $one;
				if($c->getComment()){break;}
			}
			$c = new DBColumn($c, null, null, $c->table);
		} else{
			$c = new DBColumn(null, $name, $mysqlType);
		}
		$ex = str_replace('"', '', QMStr::toString($value));
		$c->setComment("Example: $ex");
		return $c;
	}
	public function getModifyStatement(string $table): string{
		$def = $this->getSqlDeclaration();
		return "ALTER TABLE {$table} MODIFY {$def};";
	}
	public function getAddStatement(string $table): string{
		$def = $this->getSqlDeclaration();
		return "ALTER TABLE {$table} ADD COLUMN {$def};";
	}
	/**
	 * @return string
	 */
	protected function getNotNullStatement(): string{
		$notNull = ($this->getNotnull()) ? "NOT NULL" : "NULL";
		return $notNull;
	}
	public function getSqlDeclaration(): string {
		return "{$this->getName()} {$this->getSqlTypeAndComment()}";
	}
	public function getSqlTypeAndComment(): string {
		$notNull = $this->getNotNullStatement();
		$t = $this->getType();
		$type = $t->getSQLDeclaration($this->toArray(), new MySQL57Platform());
		$str = "{$type} {$notNull}";
		if($c = $this->getComment()){
			$str .= " COMMENT '{$c}'";
		}
		return $str;
	}
    public function snakize(){
	    $name = $this->getName();
	    $name_snakized = str_replace(' ', '_', $name);
	    if($name_snakized != $name){
		    $this->rename($name_snakized);
	    }
    }
	public function rename(string $newName){
		$old = $this->getName();
		if($newName != $old){
			$def = $this->getSqlTypeAndComment();
			$tableName = $this->table->getName();
			$db = $this->db();
			$db->statement("alter table $tableName
    change `$old` $newName $def");
		}
	}
	/**
	 * @return QMDB
	 */
	public function db(): QMDB{
		return $this->table->db();
	}
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->db()->getName().".".$this->getName();
	}
	public function isTimestamp(): bool{
		$type = $this->getType()->getName();
		return $type === Type::DATETIME;
	}

    public function getMaxLength(): ?int
    {
        if($this->isText()){
            return QMDB::MAX_TEXT_FIELD_LENGTH;
        }
        if($this->isDateTime()){
            return 20;
        }
        return $this->getLength();
    }

    public function createMigration(): string
    {
        $dbType = $this->getType()->getName();
        $table = $this->table->getName();
        $column = $this->getName();
        $connectionName = $this->getConnectionName();
        $path = Migrations::makeMigration($table."_".$column,
"        Schema::connection('$connectionName')->table('$table', function (Blueprint \$table) {
            \$table->$dbType('$column')->nullable()->change();
        });

");
        return $path;
    }

    private function getConnectionName(): string
    {
        return $this->getDBTable()->getConnectionName();
    }

    private function getDBTable(): ?DBTable
    {
        return $this->table;
    }

    public function isText(): bool
    {
        $type = $this->getType()->getName();
        return $type === Type::TEXT;
    }

    private function isDateTime(): bool
    {
        $type = $this->getType()->getName();
        return $type === Type::DATETIME;
    }
}
