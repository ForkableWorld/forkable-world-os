<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
// The connect instructions describe what parameters and endpoint to use to connect to the given data provider.
class ConnectInstructions {
    /**
     * The url to send parameters to
     * @var string
     */
    public $url;
    /**
     * The parameters to send (array of ConnectParameter)
     * @var array
     */
    public $parameters;
    /**
     * If true the client should use a popup to connect this connector
     * @var bool
     */
    public $usePopup;
	public $text;
	/**
	 * @param string $url
	 * @param array $parameters
	 * @param bool $usePopup
	 * @param string|null $text
	 */
    public function __construct(string $url, array $parameters, bool $usePopup, string $text = null){
        $this->url = $url;
        $this->parameters = $parameters;
        $this->usePopup = $usePopup;
		$this->text = $text;
    }
    /**
     * @return ConnectParameter[]
     */
    public function getParameters(): array{
        return $this->parameters;
    }
}
