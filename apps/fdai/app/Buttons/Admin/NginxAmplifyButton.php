<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class NginxAmplifyButton extends AdminButton {
	public $title = "Nginx Amplify";
	public $image = ImageUrls::ESSENTIAL_COLLECTION_INTERNET;
	public $fontAwesome = FontAwesome::INTERNET_EXPLORER;
	public $tooltip = "Server performance charts";
	public $link = "https://amplify.nginx.com/dashboard/23570";
}
