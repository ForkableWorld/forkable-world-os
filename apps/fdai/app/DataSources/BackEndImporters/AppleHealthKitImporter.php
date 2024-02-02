<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources\BackEndImporters;
use App\DataSources\QMSpreadsheetImporter;
use App\Logging\QMLog;
use SimpleXMLElement;
class AppleHealthKitImporter extends QMSpreadsheetImporter {
    public static function import(){
        $xml = self::readFile('/vagrant/tmp/exportar.xml');
        $records = $xml->records;
        foreach ($records as $line) {
            QMLog::info($records);
        }
    }
    /**
     * @param string $path
     * @return SimpleXMLElement
     */
    public static function readFile(string $path): SimpleXMLElement{
        $xmlString = file_get_contents($path);
        $xml = new SimpleXMLElement($xmlString);
        return $xml;
    }
}
