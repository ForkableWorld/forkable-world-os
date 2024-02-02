<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\QMFileNotFoundException;
use App\Repos\ResponsesRepo;
use App\Types\QMArr;
use App\Types\QMStr;
use App\Utils\AppMode;
use Illuminate\Http\Response;
class QMConnectorResponse extends Response {
	private QMConnector $connector;
	/**
	 * @param string $content
	 * @param int $status
	 * @param array $headers
	 * @param QMConnector $c
	 */
	public function __construct(string $content, int $status, array $headers, QMConnector $c){
		$this->connector = $c;
		parent::__construct(new Response($content, $status, $headers));
		$this->saveToResponseRepoIfNecessary();
	}
	protected function saveToResponseRepoIfNecessary(){
		if(AppMode::isApiRequest()){return;}
		$c = $this->connector;
		try {
			ResponsesRepo::saveResponse($c->getCurrentUrl(), $this->getOriginalContent(), $c->getResponseType(), $c->getUserId());
		} catch (InvalidFilePathException | QMFileNotFoundException $e) {
			le($e);
		}
	}
	public function toArray(): array{
		return QMArr::toArray($this->getOriginalContent());
	}
	public function toObj(): \stdClass {
		return json_decode($this->getOriginalContent());
	}
	/**
	 * @return string
	 */
	public function __toString() {
		return QMStr::toString($this->getOriginalContent());
	}
	/**
	 * @return string
	 */
	public function print(): string{
		return \App\Logging\QMLog::print_r($this->getOriginalContent(), true);
	}
}
