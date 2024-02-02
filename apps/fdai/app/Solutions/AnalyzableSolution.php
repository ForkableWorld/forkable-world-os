<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Solutions;
use App\Traits\FileTraits\IsSolution;
use App\Traits\QMAnalyzableTrait;
abstract class AnalyzableSolution extends ModelSolution {
	use IsSolution;
	protected $analyzable;
	/**
	 * AnalyzableSolution constructor.
	 * @param QMAnalyzableTrait|\App\VariableRelationships\QMUserVariableRelationship $analyzable
	 */
	public function __construct($analyzable = null){
		if($analyzable){
			$this->analyzable = $analyzable;
			parent::__construct($analyzable->l());
		}
	}
	public function getDocumentationLinks(): array{
		if($this->links){
			return $this->links;
		}
		$links = parent::getDocumentationLinks();
		return $this->links = array_merge($links, $this->getAnalyzable()->getUrls());
	}
	/**
	 * @return QMAnalyzableTrait|\App\VariableRelationships\QMUserVariableRelationship
	 */
	public function getAnalyzable(){
		return $this->analyzable;
	}
}
