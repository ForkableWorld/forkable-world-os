<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseExporting;
class Exporting extends BaseExporting {
	public $enabled = true; //true
	public $error; //undefined
	public $fallbackToExportServer; //true
	public $filename; //chart
	public $formAttributes; //undefined
	public $libURL; //https; ////code.highcharts.com/{version}/lib
	public $menuItemDefinitions; //{"viewFullscreen"; // {}, "printChart"; // {}, "separator"; // {}, "downloadPNG"; // {}, "downloadJPEG"; // {}, "downloadPDF"; // {}, "downloadSVG"; // {}}
	public $printMaxWidth; //780
	public $scale; //2
	public $showTable; //false
	public $sourceHeight; //undefined
	public $sourceWidth; //undefined
	public $tableCaption; //undefined
	public $type; //image/png
	public $url; //https; ////export.highcharts.com/
	public $useMultiLevelHeaders; //true
	public $useRowspanHeaders; //true
	public $width; //undefined
}
