<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Pages;
use App\Models\Variable;
class VariableIndexPage extends IndexPage {
	public static function getClass(): string{
		return Variable::class;
	}
}
