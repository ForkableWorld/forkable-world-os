<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Models\Variable;
use App\Properties\Variable\VariableSynonymsProperty;
use App\Storage\DB\Migrations;
use App\Types\QMStr;
use Spatie\Tags\HasTags;
trait HasSynonyms {
	use HasTags;
	protected $lowerCaseSynonyms;
	/**
	 * @param string $synonym
	 * @return null|static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function findBySynonym(string $synonym){
		if($variable = static::findBySynonymInMemory($synonym)){
			return $variable;
		}
		$qb = static::query()->where(Variable::FIELD_SYNONYMS, "LIKE", "%" . $synonym . "%");
		$variables = $qb->get();
		if(!$variables->count()){
			return null;
		}
		foreach($variables as $v){
			$v->addToMemory();
		}
		foreach($variables as $v){
			if($v->inSynonyms($synonym)){
				return $v;
			}
		}
		return null;
	}
	/**
	 * @param string $synonym
	 * @return null|Variable
	 */
	public static function findBySynonymInMemory(string $synonym): ?Variable{
		$globals = static::getAllFromMemoryIndexedByUuidAndId();
		foreach($globals as $v){
			if($v->isNameOrSynonym($synonym)){
				return $v;
			}
		}
		return null;
	}
	public function isNameOrSynonym(string $string): bool{
		return $this->inSynonyms($string);
	}
	/**
	 * @param $string
	 * @return bool
	 */
	public function inSynonyms(string $string): bool{
		$string = strtolower($string);
		$synonyms = $this->getLowerCaseSynonyms();
		return in_array($string, $synonyms, true);
	}
	/**
	 * @return array
	 */
	public function getLowerCaseSynonyms(): array{
		if($this->lowerCaseSynonyms !== null){
			return $this->lowerCaseSynonyms;
		}
		$synonyms = $this->getOrGenerateSynonyms();
		if(empty($synonyms)){
			return [];
		}
		foreach($synonyms as $synonym){
			$this->lowerCaseSynonyms[] = strtolower($synonym);
		}
		return $this->lowerCaseSynonyms;
	}
	/**
	 * @param string|int $nameOrId
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	abstract public static function findByNameIdOrSynonym($nameOrId);
	/**
	 * @return array
	 */
	protected function getOrGenerateSynonyms(): array{
		if(is_object($this->synonyms)){
			$this->synonyms = json_decode(json_encode($this->synonyms), true);
		}
		if($this->synonyms === null){
			return $this->generateSynonyms();
		}
		return $this->synonyms;
	}
	public static function generateSynonymMigration(){
		$singular = QMStr::snakize((new \ReflectionClass(static::class))->getShortName());
		$table = static::TABLE;
		Migrations::makeMigration(static::TABLE . "_synonyms", "
create table " . $singular . "_synonyms
(
	id int auto_increment,
	synonym varchar(125) null,
	" . $singular . "_id int null,
	constraint " . $singular . "_synonyms_pk
		primary key (id),
	constraint " . $singular . "_synonyms_" . $table . "_id_fk
		foreign key (" . $singular . "_id) references $table (id)
);

create unique index " . $singular . "_synonyms_synonym_uindex
	on " . $singular . "_synonyms (synonym);
        ");
	}
}
