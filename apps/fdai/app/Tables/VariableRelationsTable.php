<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Exceptions\NotEnoughDataException;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Types\QMArr;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\Variables\QMVariable;
abstract class VariableRelationsTable extends BaseTable {
	protected $orderColumnIndex = 1;
	protected $orderDirection = self::DESC;
	/**
	 * @var Variable
	 */
	protected $variable;
	/**
	 * @param Variable $variable
	 * @throws NotEnoughDataException
	 */
	public function __construct($variable){
		$variable = $variable->getVariable();
		$this->variable = $variable;
		$this->setId($variable->getUniqueIndexIdsSlug() . "-" . QMStr::slugify(static::class));
		parent::__construct();
		$this->setDataFromVariable();
		//$this->addImageColumn();
		$this->addNameColumn();
	}
	/**
	 * @throws NotEnoughDataException
	 */
	abstract protected function setDataFromVariable();
	abstract protected function getVariableFromRow($row): Variable;
	abstract protected function getNameColumnTitle(): string;
	abstract protected function getNameColumnDescription(): string;
	protected function addNameColumn(){
		$this->column()->title($this->getNameColumnTitle() . " " . FontAwesome::html(FontAwesome::QUESTION_CIRCLE) .
				" ")->attr('th', 'title', $this->getNameColumnDescription())->value(function($row){
				$b = $this->getVariableFromRow($row)->getButton();
				$b->setBadgeText(null);
				$link = $b->getLink();
				return $link;
			})->attr('td', 'data-order', function($row){
				$v = $this->getVariableFromRow($row);
				$name = str_replace('"', '', $v->name);
				return $name;
			})->add();
	}
	/**
	 * @return $this
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function addImageColumn(){
		$this->column()->title(' ')->value(function($row){
				$link = $this->getVariableFromRow($row)->getDataLabImageLink([],
						"height: 32px; border-radius: 0; cursor: pointer; object-fit: scale-down; margin: auto;");
				return $link;
			})->add();
		return $this;
	}
	/**
	 * @return UserVariable|Variable|QMVariable
	 */
	public function getVariable(){
		return $this->variable;
	}
    /**
     * @param $data
     * @return BaseTable
     * @throws NotEnoughDataException
     * @noinspection PhpMissingReturnTypeInspection
     */
	public function setData(mixed $data): static{
        if(QMArr::empty($data)){
            $v = $this->getVariable();
            throw new NotEnoughDataException($v, "Not enough data for " . $this->getId(), "");
        }
        return parent::setData($data);
    }
}
