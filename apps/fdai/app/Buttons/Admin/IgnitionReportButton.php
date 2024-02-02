<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class IgnitionReportButton extends AdminButton {
	public $accessibilityText = 'ignition Report';
	public $action = 'admin/ignitionReport';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-ignition-report-button';
	public $image = 'https://laracasts.s3.amazonaws.com/series/thumbnails/whats-new-in-laravel-6.png';
	public $link = '/admin/ignitionReport';
	public $parameters = [];
	public $target = 'self';
	public $text = 'ignition Report';
	public $title = 'ignition Report';
	public $tooltip = 'ignition Report ';
	public $visible = true;
}
