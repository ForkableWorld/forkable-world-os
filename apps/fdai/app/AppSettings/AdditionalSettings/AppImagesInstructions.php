<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
class AppImagesInstructions {
    public $appIcon;
    public $splashScreen;
    public $textLogo;
    /**
     * SocialLinks constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        $this->appIcon = '<p>Ideally, a 512px by 512px  icon or square logo png image with a transparent background.  <a href="http://code.ionicframework.com/resources/icon.psd">Photoshop Icon Template</a></p><br>';
        $this->splashScreen = 'Splash screen dimensions vary for each platform, device, and orientation, so a square source image is required to generate each of the various screen sizes.
            The source image’s minimum dimensions should be 2208×2208 px, and the artwork should be centered within the square, because each generated image will be center cropped into landscape and portrait images.
            The splash screen’s artwork should roughly fit within a center square (1200×1200 px). <a href="http://code.ionicframework.com/resources/splash.psd">This template</a> provides the recommended size and guidelines about artwork’s safe zone.';
        $this->textLogo = 'Ideally, a 100px high by 500px wide png with a transparent background containing the name of your app that looks good on a dark background.';
    }
}
