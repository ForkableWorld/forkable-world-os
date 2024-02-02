<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AdditionalSettings;
use App\AppSettings\AppSettings;
class BuildSettingInstructionLinks {
    public $androidReleaseKeystoreFile;
    public $androidReleaseKeystorePassword;
    public $androidReleaseKeyAlias;
    public $androidReleaseKeyPassword;
    public $xwalkMultipleApk;
    /**
     * BuildSettings constructor.
     * @param AppSettings $appSettings
     */
    public function __construct($appSettings = null){
        $this->androidReleaseKeystoreFile = "https://developer.android.com/training/articles/keystore.html";
        $this->androidReleaseKeystorePassword = "https://developer.android.com/training/articles/keystore.html";
        $this->androidReleaseKeyAlias = "https://developer.android.com/training/articles/keystore.html";
        $this->androidReleaseKeyPassword = "https://developer.android.com/training/articles/keystore.html";
        $this->xwalkMultipleApk = "https://crosswalk-project.org/documentation/about/faq.html";
    }
}
