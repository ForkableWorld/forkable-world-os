<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\DataSources\Connectors\GmailConnector;
use App\Properties\User\UserIdProperty;
use App\Variables\QMVariable;
abstract class EmailImportConnector extends GmailConnector
{
    public function getGmailService(){
        $mike = GmailConnector::getByUserId(UserIdProperty::USER_ID_MIKE);
        return $mike->getGmailService();
    }
    /**
     * @param $opt_param
     * @return \Google_Service_Gmail_ListMessagesResponse
     */
    public function setMessagesResponse($opt_param){
        $opt_param['sender'] = $this->getCredentialsArray('email');
        return parent::setMessagesResponse($opt_param);
    }
    public function getConnectInstructions(): ?ConnectInstructions{
        $parameters = [new ConnectParameter('Email', 'email', 'text')];
        return $this->getNonOAuthConnectInstructions($parameters, 'Enter Your MoodPanda Email');
    }
    /**
     * @param QMVariable|\App\Models\Variable $variable
     * @param array $urlParams
     * @return string
     */
    public function setInstructionsHtml($variable, array $urlParams = []): string{
        $paragraph =
            '<p>
            Get <a href="'.$this->getItUrl.'">'.$this->displayName.
            ' here</a> and use it to record your '.
            $variable->getOrSetVariableDisplayName().
            '.  Once you have a '.
            $this->getLinkedDisplayNameHtml().
            ' account, you can import your data from the <a href="'.
            $this->getConnectWebPageUrl($urlParams).
            '">Import Data page</a>.
            </p>';
        return $this->instructionsHtml = $paragraph.'
            <p>
        '.QMDataSource::INSTRUCTIONS_SUFFIX."
            </p>";
    }
}
