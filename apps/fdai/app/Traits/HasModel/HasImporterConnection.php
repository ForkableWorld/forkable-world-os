<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\Connection;
trait HasImporterConnection {
	/**
	 * @return Connection
	 */
	public function getImporterConnection(): ?Connection{
		$id = $this->getImporterConnectionId();
		if(!$id){
			return null;
		}
		return Connection::findInMemoryOrDB($id);
	}
	public function getImporterConnectionButton(): ?QMButton{
		$id = $this->getImporterConnectionId();
		if(!$id){
			return null;
		}
		return $this->getImporterConnection()->getButton();
	}
	public function getImporterConnectionLink(): string{
		$id = $this->getImporterConnectionId();
		if(!$id){
			return "N/A";
		}
		return Connection::generateDataLabShowButton($id)->getNameLink();
	}
	public function getImporterConnectionId(): ?int{
		return $this->getAttribute('connection_id');
	}
	public function getConnectionLink(): string{
		return $this->getImporterConnectionLink();
	}
}
