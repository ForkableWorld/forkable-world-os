<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\Connectors;
use App\DataSources\ConnectInstructions;
use App\DataSources\PasswordConnector;
use App\Slim\Controller\Connector\ConnectException;
use App\Slim\Controller\Connector\ConnectorNonOauthConnectResponse;
class MintConnector extends PasswordConnector {
    protected const DEVELOPER_CONSOLE = null;
    
    
    
    
	protected const AFFILIATE = false;
	protected const BACKGROUND_COLOR = '#4cd964';
	protected const CLIENT_REQUIRES_SECRET = false;
	protected const DEFAULT_UNIT_ABBREVIATED_NAME = '$';
	protected const DEFAULT_VARIABLE_CATEGORY_NAME = 'Payments';
	public const DISPLAY_NAME = 'Mint';
	protected const ENABLED = 0;
	protected const GET_IT_URL = null;
	public const ID = 80;
	public const IMAGE = 'https://static-s.aa-cdn.net/img/gp/20600000039023/Vd9ubTf3GHgogznhGqYLb-hFtx6gqhZ5h5wBzSf-1Wf_GsFHRf1Lk_HX0muiCTp1fL_u=w300?v=1';
	protected const LOGO_COLOR = '#2d2d2d';
	protected const LONG_DESCRIPTION = 'Import your transactions from the Mint.com website. Mint allows you to manage your money, pay your bills and track your credit score with Mint.';
	public const NAME = 'mint';
	protected const PREMIUM = false;
	protected const SHORT_DESCRIPTION = 'Automatically import financial transactions and categories';
	public $affiliate = self::AFFILIATE;
	public $backgroundColor = self::BACKGROUND_COLOR;
	public $clientRequiresSecret = self::CLIENT_REQUIRES_SECRET;
	public $defaultUnitAbbreviatedName = self::DEFAULT_UNIT_ABBREVIATED_NAME;
	public $defaultVariableCategoryName = self::DEFAULT_VARIABLE_CATEGORY_NAME;
	public $displayName = self::DISPLAY_NAME;
	public $enabled = self::ENABLED;
	public $getItUrl = self::GET_IT_URL;
	public $id = self::ID;
	public $image = self::IMAGE;
	public $logoColor = self::LOGO_COLOR;
	public $longDescription = self::LONG_DESCRIPTION;
	public $name = self::NAME;
	public $premium = self::PREMIUM;
	public $shortDescription = self::SHORT_DESCRIPTION;
    /**
     * @return ConnectInstructions
     */
    public function getConnectInstructions(): ?ConnectInstructions{
        return $this->getUserNamePasswordConnectInstructions();
    }
	/**
	 * @param array $parameters
	 * @return ConnectorNonOauthConnectResponse
	 * @throws ConnectException
	 */
    public function connect($parameters): ConnectorNonOauthConnectResponse{
        if(empty($parameters['username'])){
            throw new ConnectException($this, 'No username specified');
        }
        if(empty($parameters['password'])){
            throw new ConnectException($this, 'No password specified');
        }
        // ['username', 'password', 'thx_guid', 'ius_session']
        $this->storeCredentials($parameters);
        if(isset($parameters['thx_guid'])){
            $this->importData();
        }
        return new ConnectorNonOauthConnectResponse($this);
    }
    public function importData(): void {
        // todo
    }
}
