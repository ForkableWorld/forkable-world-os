<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Http\Controllers;
use Illuminate\View\Factory as ViewFactory;
class QMWPController extends Controller {
	public function bargraphScatterplotTimeline(ViewFactory $view){
		return $view->make('wp_includes/bargraph-scatterplot-timeline');
	}
	public function timeline(ViewFactory $view){
		return $view->make('wp_includes/timeline');
	}
}
