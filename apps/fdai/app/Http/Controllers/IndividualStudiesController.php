<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use App\Studies\QMUserStudy;
class IndividualStudiesController extends Controller {
	public function index(): string{
		return QMUserStudy::getIndexHtml();
	}
}
