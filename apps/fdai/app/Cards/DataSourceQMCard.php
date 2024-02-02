<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\DataSources\QMDataSource;
class DataSourceQMCard extends QMCard {
	private $dataSource;
	/**
	 * @param QMDataSource $dataSource
	 */
	public function __construct($dataSource){
		$this->dataSource = $dataSource;
		$this->setAvatar($dataSource->getImage());
		$this->setImage($dataSource->getImage());
		$this->setSubTitle($dataSource->getShortDescription());
		$this->setContentAndHtmlContent($dataSource->getLongDescription());
		$this->setDefaultButtons();
		$this->setTitle($dataSource->displayName);
		parent::__construct($dataSource->displayName);
	}
}
