<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\OAClient;
use App\Models\OAClient;
use App\Traits\PropertyTraits\OAClientProperty;
use App\Properties\Base\BaseRedirectUriProperty;
use Database\Seeders\DatabaseSeeder;
class OAClientRedirectUriProperty extends BaseRedirectUriProperty
{
    use OAClientProperty;
    public $table = OAClient::TABLE;
    public $parentClass = OAClient::class;
	public function validate(): void{
		parent::validate();
		$redirectUris = $this->getDBValue();
		if(!empty($redirectUris)){
			$redirectUris = preg_split('/\r\n|[\r\n]/', $redirectUris);
			$redirectUris = array_unique($redirectUris);
			foreach($redirectUris as $uri){
				if(!$this->isUriLocalHostOrHttps($uri)){
                    if(DatabaseSeeder::isReprocessingSeed()){
                        $this->setValue("http://localhost");
                    } else {
                        $this->throwException('Redirects must contain https or localhost');
                    }
				}
			}
		}
	}
	/**
	 * @param $uri
	 * @return bool
	 */
	private function isUriLocalHostOrHttps($uri): bool{
		return strpos($uri, 'https://') !== false || strpos($uri, 'localhost') !== false;
	}
}
