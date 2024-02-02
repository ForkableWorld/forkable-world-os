<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Github;
use App\Buttons\GithubButton;
class PhpHighchartsExporterGithubButton extends GithubButton {
	public $image = 'https://avatars3.githubusercontent.com/u/2808553?v=4';
	public $fontAwesome = 'fab fa-github';
	public $action = 'https://api.github.com/repos/mikepsinn/php-highcharts-exporter';
	public $id = 'github-button';
	public $link = 'https://api.github.com/repos/mikepsinn/php-highcharts-exporter';
	public $text = 'Php Highcharts Exporter';
	public $title = 'Php Highcharts Exporter';
	public $tooltip = 'Generate Charts on Server Side using Highcharts without NodeJS';
	public $menus = [];
}
