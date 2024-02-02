<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\Connection;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasConnection {
	public function getConnectionId(): int{
		$nameOrId = $this->getAttribute('connection_id');
		return $nameOrId;
	}
	public function getConnectionButton(): QMButton{
		$connection = $this->getConnection();
		if($connection){
			return $connection->getButton();
		}
		return Connection::generateDataLabShowButton($this->getConnectionId());
	}
	/**
	 * @return Connection
	 */
	public function getConnection(): Connection{
		if($this instanceof BaseProperty && $this->parentModel instanceof Connection){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('connection')){
			return $l;
		}
		$id = $this->getConnectionId();
		$connection = Connection::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['connection'] = $connection;
		}
		if(property_exists($this, 'connection')){
			$this->connection = $connection;
		}
		return $connection;
	}
	public function getConnectionNameLink(): string{
		return $this->getConnection()->getDataLabDisplayNameLink();
	}
	public function getConnectionImageNameLink(): string{
		return $this->getConnection()->getDataLabImageNameLink();
	}
}
