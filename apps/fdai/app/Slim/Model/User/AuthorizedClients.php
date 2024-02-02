<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\User;
use App\AppSettings\BaseApplication;
use App\Models\Study;
use App\Models\User;
use App\Studies\QMCohortStudy;
class AuthorizedClients {
	/**
	 * @var \App\DataSources\QMClient[]
	 */
	public $studies = [];
	public $apps = [];
	public $individuals = [];
	/**
	 * AuthorizedClients constructor.
	 * @param int $userId
	 */
	public function __construct(int $userId){
		$u = User::findInMemoryOrDB($userId);
		$QMUser = $u->getQMUser();
		$tokens = $QMUser->getAllValidAccessTokens();
		foreach($tokens as $token){
			if($token->isStudy()){
				$this->studies[] = $token->getClient();
			} elseif($token->isPhysician()){
				$this->individuals[] = $token->getClient();
			} else{
				$this->apps[] = $token->getClient();
			}
		}
	}
	/**
	 * @return BaseApplication[]
	 */
	public function getIndividuals(): array{
		return $this->individuals;
	}
	/**
	 * @return QMCohortStudy[]
	 */
	public function getStudies(): array{
		$clients = $this->studies;
		$studies = [];
		foreach($clients as $app){
			$s = Study::findInMemoryOrDB($app->clientId);
			$studies[] = $s->getOrSetQMStudy();
		}
		return $studies;
	}
}
