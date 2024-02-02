<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
use App\Slim\Model\QMResponseBody;
use App\Slim\Model\User\QMUser;
class ConnectorResponse extends QMResponseBody {
	public $userId;
	public $methodName;
	public $connector;
	public $connectors;
	public $user;
	public $connection;
	/**
	 * @param QMConnector $connector
	 * @param string $methodName
	 */
	public function __construct(QMConnector $connector, string $methodName){
		parent::__construct();
		$this->methodName = $methodName;
		$this->setConnector($connector);
		$this->connection = $connector->getConnectionIfExists();
		$this->setConnectors();
	}
	/**
	 * @return null|QMConnector
	 */
	public function getConnector(): ?QMConnector{
		return $this->connector;
	}
	/**
	 * @param null|QMConnector $connector
	 */
	public function setConnector(?QMConnector $connector){
		//$this->connector = json_decode(json_encode($connector), false);
		$this->connector = $connector;
		$this->avatar = $connector->image;
		if($connector->userId){
			$this->userId = $connector->userId;
		}
	}
	/**
	 * @return QMUser
	 */
	private function getUser(): ?QMUser{
		$id = $this->getUserId();
		if(!$id){
			return null;
		}
		// Why is this clone necessary?
		return $this->user = clone QMUser::find($id);
	}
	/**
	 * @return int
	 */
	private function getUserId(): ?int{
		if(!$this->userId){
			$this->userId = $this->getConnector()->getUserId();
		}
		return $this->userId;
	}
	/**
	 * @return QMConnector[]
	 */
	public function setConnectors(): void{
		if($u = $this->getUser()){
			$connectors = $u->getOrSetConnectors();
		} else{
			$connectors = QMConnector::getAnonymousConnectors();
		}
		ksort($connectors);
		foreach($connectors as $c){
			$obj = json_decode(json_encode($c), false);
			if(!$obj){
				le("could not encode $obj");
			}
			$c->getButtons();
			$this->connectors[] = $c;
		}
	}
}
