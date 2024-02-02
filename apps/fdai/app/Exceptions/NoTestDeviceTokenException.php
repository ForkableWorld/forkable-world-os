<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use App\Utils\IonicHelper;
use App\Storage\Firebase\FirebaseGlobalPermanent;
class NoTestDeviceTokenException extends Exception implements ProvidesSolution
{
    private string $firebaseKey;
    public function __construct($firebaseKey){
        $this->firebaseKey = $firebaseKey;
        parent::__construct("Could not get test device token from Firebase with key $firebaseKey! Check out ".
            $this->getFBURL());
    }
    public function getSolution(): Solution{
        return BaseSolution::create("Login in Again")
            ->setSolutionDescription("Please log out and in and go to inbox so a new one is saved. ")
            ->setDocumentationLinks([
                "Check Firebase" => $this->getFBURL(),
                "Log Out In Settings" => IonicHelper::getDevUrl('settings'),
            ]);
    }

    /**
     * @return string
     */
    private function getFBURL(): string
    {
        return FirebaseGlobalPermanent::url($this->firebaseKey);
    }
}
