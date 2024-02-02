<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Exceptions\SlowQueryException;
use App\Storage\DB\Adminer;
use App\Storage\DB\Migrations;
class SlowQuerySolution extends BaseRunnableSolution {
	public string $wheres;
	public string $table;
	public string $sql;
	public float $time;
	/**
	 * SlowQuerySolution constructor.
	 * @param SlowQueryException|null $slowQueryException
	 */
	public function __construct(SlowQueryException $slowQueryException = null){
		if($slowQueryException){
			$query  =$slowQueryException->queryExecuted;
			$this->wheres = $query->getWhere();
			$this->table = $query->getTable();
			$this->sql = $query->sql;
			$this->time = $query->time;
		}
	}
	public function getSolutionTitle(): string{
		return "Profile this Query in MySQL Workbench";
	}
	public function getSolutionDescription(): string{
		return "Profile, create indexes and check query time until fixed. ";
	}
	public function getSolutionActionDescription(): string{
		return "Generate index migration";
	}
	public function getRunButtonText(): string{
		return "Generate Index Migration";
	}
	/**
	 * @param array $parameters
	 * @return string
	 * @throws \Exception
	 */
	public function run(array $parameters = []): string{
		foreach($parameters as $key => $value){
			$this->$key = $value;
		}
		$fields = $this->wheres;
		$indexName = $this->table."_".implode("_", $fields)."_index";
		$fieldsStr = implode(", ", $fields);
		return Migrations::makeMigration($this->table."_index_for_slow_query", "create index $indexName
	        on $this->table ($fieldsStr);");
	}
	public function getRedirectUrl(): string{
		return Adminer::getStatementUrl($this->sql);
	}
	public function getRunParameters(): array{
		return $this->toArray();
	}
	public function getDocumentationLinks(): array{
		$links = [];
		if($this->table){
			$links = ['table structure' => Adminer::getTableSelectUrl($this->table)];
		} else {
			$msg = "Could not Create TableSelectUrl table not provided to ".__METHOD__;
			$links[$msg] = $msg;
		}
		if($this->sql){
			$links['Create Index'] = Migrations::getUrlToCreateIndexMigration($this->sql);
		} else {
			$msg = "Could not Create Index because sql not provided to ".__METHOD__;
			$links[$msg] = $msg;
		}
		return $links;
	}
}
