<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Formulas;
use App\Logging\ConsoleLog;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Storage\DB\Migrations;
use App\Storage\DB\Writable;
class SQLView
{
	protected const HUGE_TABLES = [
		Measurement::TABLE,
		UserVariableRelationship::TABLE,
	];
	public $name;
	public $columns;
	public $fields;
	public $foreignKey;
	public $sql;
	public $viewSourceTable;
	/**
	 * @var string
	 */
	protected $analyzableTable;
	/**
	 * @var string
	 */
	protected $analyzableIdField;
	public function __construct(array $arr, string $analyzableTable, string $analyzableIdField){
		$this->analyzableTable = $analyzableTable;
		$this->analyzableIdField = $analyzableIdField;
		$this->name = self::generateName($this->viewSourceTable = $arr['table'], $this->foreignKey = $arr['foreign_key']);
		$this->columns = [$this->foreignKey];
		$this->sql = $arr['sql'] ?? null;
	}
	public static function generateName(string $table, string $foreignKey): string {
		return $table."_aggregated_by_".$foreignKey;
	}
	protected function createView(){
		$viewSourceTable = $this->viewSourceTable;
		$foreignKey = $this->foreignKey;
		$sql = "CREATE OR REPLACE VIEW `$this->name` AS\n\tSELECT\n\t\t";
		$sql .= implode(",\n\t\t", $this->columns);
		$sql .= "\n\tfrom $viewSourceTable \n\t" . "where $viewSourceTable.deleted_at is NULL\n" .
		        "group by $foreignKey;\n";
		Migrations::pdoStatement($this->sql = $sql);
	}
	protected function getUpdateQuery(){
		$table = $this->analyzableTable;
		$updateQuery = "update $table
                left join $this->name on
                    $table.$this->analyzableIdField
                        = $this->name.$this->foreignKey
                        SET
                        ";
		$setRows = [];
		foreach($this->fields as $field){
			$setRows[] = "$table.$field = $this->name.$field";
		}
		$updateQuery .= implode(",\n\t\t", $setRows).";\n";
		return $updateQuery;
	}
	public function executeUpdate(){
		if(!$this->weShouldExecute()){return;}
		$this->createView();
		$updateQuery = $this->getUpdateQuery();
		$start = microtime(true);
		Writable::statementStatic($updateQuery);
		$duration = microtime(true) - $start;
		ConsoleLog::info("Took $duration seconds:
                $updateQuery");
		$this->setCountFieldsToZeroWhereNull();
	}
	protected function setCountFieldsToZeroWhereNull(){
		$thisTable = $this->analyzableTable;
		foreach($this->fields as $field){
			if(str_starts_with($field, 'number_')){
				Writable::statementStatic("update $thisTable set $field = 0
                        where $field is null");
			}
		}
	}
	protected function weShouldExecute(): bool {
		$viewSourceTable = $this->viewSourceTable;
		if(in_array($viewSourceTable, self::HUGE_TABLES)){
			ConsoleLog::info("Skipping global analysis for view $this->name
                because $viewSourceTable is too big...");
			return false;
		}
		return true;
	}
}
