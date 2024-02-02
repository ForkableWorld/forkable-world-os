<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Exceptions\NotFoundException;
use App\Models\Measurement;
trait HasDataSource {
	/**
	 * @return QMDataSource
	 * @throws NotFoundException
	 */
	public function getQMDataSource(): QMDataSource{
		$nameOrId = $this->getDataSourceNameOrId();
		return QMDataSource::getByNameOrId($nameOrId, $this->getAttribute('user_id'));
	}
	/**
	 * @return QMDataSource
	 * @throws NotFoundException
	 */
	public function getAnonymousDataSource(): QMDataSource{
		$nameOrId = $this->getDataSourceNameOrId();
		return QMDataSource::getByNameOrId($nameOrId, null);
	}
	/**
	 * @return QMConnector
	 * @throws NotFoundException
	 */
	public function getQMConnector(): QMConnector{
		/** @var QMConnector $c */
		$c = $this->getQMDataSource();
		return $c;
	}
	public function getDataSourceButton(): QMButton{
		return $this->getQMDataSource()->getButton();
	}
	public function getDataSourceLink(): string{
		return $this->getAnonymousDataSource()->getButton()->getImageTextLink();
	}
	/**
	 * @return QMDataSource
	 * @throws NotFoundException
	 */
	public function getConnectorLink(): string{
		try {
			$s = $this->getQMDataSource();
			if($s){
				return $s->getDataLabDisplayNameLink();
			}
		} catch (\Throwable $e) {
			return $e->getMessage();
		}
		return "N/A";
	}
	public function getImage(): string{
		if(!$this->hasValidId()){
			return static::DEFAULT_IMAGE;
		}
		return $this->getAnonymousDataSource()->getImage();
	}
	/**
	 * @return string|int
	 */
	private function getDataSourceNameOrId(){

		foreach([
			Measurement::FIELD_SOURCE_NAME,
			Measurement::FIELD_CONNECTOR_ID,
			Measurement::FIELD_CLIENT_ID,
		] as $key){
			$nameOrId = $this->getAttribute($key);
			if($nameOrId){break;}
		}
		if(is_array($nameOrId)){
			foreach($nameOrId as $value){
				if($value){
					return $value;
				}
			}
			return null;
		}
		return $nameOrId;
	}
	public function getDataSourceImageTextLink(): string{
		return $this->getAnonymousDataSource()->getButton()->getImageTextLink();
	}
	public function getDataSourceDisplayName(): string{
		return $this->getAnonymousDataSource()->getTitleAttribute();
	}
}
