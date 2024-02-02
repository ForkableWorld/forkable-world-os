<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Cards;
class SocialLinksQMCard extends QMCard {
	/**
	 * @return string
	 */
	public static function getBrownSocialLinksHtml(): string{
		$html = '
            <tbody>
                <tr>
                    <td style="background-color:#414141; color:#bbbbbb; font-size:12px;">
                 <br> <br> Any interesting discoveries? <br> Share them with the world!  <br>
                </td>
                </tr>
                <tr>
                     <td style="background-color:#414141;">
                      <a href="https://plus.google.com/communities/100581500031158281444" target="_blank">
                      <img src="https://www.filepicker.io/api/file/R4VBTe2UQeGdAlM7KDc4" alt="google+">
                            </a>
                         <a href="https://www.facebook.com/Quantimodology" target="_blank">
                          <img src="https://www.filepicker.io/api/file/cvmSPOdlRaWQZnKFnBGt" alt="facebook">
                            </a>
                         <a href="https://twitter.com/quantimodo" target="_blank">
                          <img src="https://www.filepicker.io/api/file/Gvu32apSQDqLMb40pvYe" alt="twitter">
                            </a>
                        <br>
                        <br>
                    </td>
                </tr>
            </tbody>
        ';
		//if(AppMode::isTestingOrStaging()){HtmlHelper::assertValidHtml($html);}
		return $html;
	}
}
