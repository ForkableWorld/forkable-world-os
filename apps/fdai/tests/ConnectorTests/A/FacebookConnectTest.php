<?php
/** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\A;
use App\Computers\ThisComputer;
use App\DataSources\CredentialStorage;
use App\Models\Connection;
use LogicException;
use Tests\SlimTests\SlimTestCase;
class FacebookConnectTest extends SlimTestCase {
	public function testPostFacebookAccessToken(){
		if(time() < strtotime(FacebookTest::FACEBOOK_DISABLED_UNTIL)){
			$this->skipTest('Waiting for update of Supplemental Terms Status at https://business.facebook.com/settings/info');
			return;
		}
		$requestBody = $this->getFacebookTestPostBody();
		CredentialStorage::truncate();
		Connection::truncate();
		ThisComputer::outputMemoryUsageIfEnabledOrDebug();
		$this->setAuthenticatedUser(1);
		$this->postApiV3('connectors/connect', $requestBody);
		$connection = Connection::whereWaiting()->first();
		if(!$connection){
			throw new LogicException("No waiting connections!");
		}
	}
	/**
	 * @return string
	 */
	private function getFacebookTestPostBody(): string{
		return '
            {
              "connectorCredentials": {
                "token": {
                  "status": "connected",
                  "authResponse": {
                    "accessToken": "test-access-token",
                    "expiresIn": "9223370508119795",
                    "session_key": true,
                    "sig": "...",
                    "userID": "778392768"
                  }
                }
              },
              "connector": {
                "providesUserProfileForLogin": true,
                "mobileConnectMethod": "facebook",
                "stdOAuthToken": null,
                "buttons": [
                  {
                    "action": null,
                    "link": null,
                    "color": "#0f9d58",
                    "additionalInformation": null,
                    "text": "Connect",
                    "ionIcon": "ion-link"
                  }
                ],
                "clientRequiresSecret": false,
                "connected": false,
                "connectError": null,
                "connectInstructions": {
                  "url": "https://www.facebook.com/v2.9/dialog/oauth?state=eyJjbGllbnRfaWQiOiJxdWFudGltb2RvIn0-&type=web_server&client_id=225078261031461&redirect_uri=https%3A%2F%2Fapp.quantimo.do%2Fapi%2Fv1%2Fconnectors%2Ffacebook%2Fconnect&response_type=code&scope=user_likes%2Cuser_posts",
                  "parameters": [],
                  "usePopup": true
                },
                "connectorClientId": "225078261031461",
                "connectorUserEmail": null,
                "connectStatus": "DISCONNECTED",
                "enabled": 1,
                "errorMessage": null,
                "lastSuccessfulUpdatedAt": null,
                "message": "Facebook is a social networking website where users may create a personal profile, add other users as friends, and exchange messages.",
                "platforms": [
                  "ios",
                  "android",
                  "web",
                  "chrome"
                ],
                "qmClient": null,
                "realId": null,
                "scopes": [
                  "user_likes",
                  "user_posts"
                ],
                "spreadsheetUpload": null,
                "totalMeasurementsInLastUpdate": null,
                "updateError": null,
                "updateRequestedAt": null,
                "updateStatus": null,
                "affiliate": false,
                "backgroundColor": "#3b579d",
                "defaultUnitAbbreviatedName": null,
                "defaultVariableCategoryName": "Social Interactions",
                "displayName": "Facebook",
                "getItUrl": "http://www.facebook.com",
                "id": 8,
                "image": "https://applets.imgix.net/https%3A%2F%2Fassets.ifttt.com%2Fimages%2Fchannels%2F29%2Ficons%2Fon_color_large.png%3Fversion%3D0?ixlib=rails-2.1.3&w=240&h=240&auto=compress&s=216b3768523a87b6eadc8f3ee46dcef7",
                "imageHtml": "<a href=\"http://www.facebook.com\"><img id=\"facebook_image\" title=\"Facebook\" src=\"https://applets.imgix.net/https%3A%2F%2Fassets.ifttt.com%2Fimages%2Fchannels%2F29%2Ficons%2Fon_color_large.png%3Fversion%3D0?ixlib=rails-2.1.3&w=240&h=240&auto=compress&s=216b3768523a87b6eadc8f3ee46dcef7\" alt=\"Facebook\"></a>",
                "linkedDisplayNameHtml": "<a href=\"http://www.facebook.com\">Facebook</a>",
                "logoColor": "#3b5998",
                "longDescription": "Facebook is a social networking website where users may create a personal profile, add other users as friends, and exchange messages.",
                "name": "facebook",
                "premium": null,
                "shortDescription": "Tracks social interaction. QuantiModo requires permission to access your Facebook \"user likes\" and \"user posts\".",
                "connnected": false
              }
            }
        ';
	}
}
