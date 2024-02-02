<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Github;
use App\Buttons\GithubButton;
class QuantimodoDesignGithubButton extends GithubButton {
	public $image = 'https://avatars3.githubusercontent.com/u/2808553?v=4';
	public $fontAwesome = 'fab fa-github';
	public $action = 'https://api.github.com/repos/mikepsinn/quantimodo-design';
	public $id = 'github-button';
	public $link = 'https://api.github.com/repos/mikepsinn/quantimodo-design';
	public $text = 'Quantimodo Design';
	public $title = 'Quantimodo Design';
	public $tooltip = 'Private repo containing all stock images and AI/PSD files. Public images are at https://github.com/mikepsinn/quantimodo-images';
	public $menus = [];
}
