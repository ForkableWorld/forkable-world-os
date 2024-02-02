<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\OpenInsider\Paths;
use App\Exceptions\DuplicateDataException;
use App\Logging\QMLog;
use App\Scrapers\BasePath;
use App\Traits\LoggerTrait;
use App\Types\QMStr;
class OpenInsiderPath extends BasePath
{
    use LoggerTrait;
    public $path = self::PATH_PS_DATA_CSV;
    public $parameters = [];
    const RATIO_OF_NUMBER_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES = "Ratio of Number of SEC-Registered Insider Buys to Sales";
    const RATIO_OF_VALUE_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES = "Ratio of Value of SEC-Registered Insider Buys to Sales";
    const NUMBER_OF_SEC_REGISTERED_INSIDER_PURCHASES = "Number of SEC Registered Insider Purchases";
    const NUMBER_OF_SEC_REGISTERED_INSIDER_SALES = "Number of SEC Registered Insider Sales";
    const VALUE_OF_SEC_REGISTERED_INSIDER_PURCHASES = "Value of SEC Registered Insider Purchases";
    const VALUE_OF_SEC_REGISTERED_INSIDER_SALES = "Value of SEC Registered Insider Sales";
    public const PATH_PS_DATA_CSV = '/ps_data.csv';
    public function getAllVariablesData(): array{
        $arr = [
            self::VALUE_OF_SEC_REGISTERED_INSIDER_PURCHASES => [],
            self::VALUE_OF_SEC_REGISTERED_INSIDER_SALES => [],
            self::RATIO_OF_NUMBER_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES => [],
            self::RATIO_OF_VALUE_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES => [],
            self::NUMBER_OF_SEC_REGISTERED_INSIDER_PURCHASES => [],
            self::NUMBER_OF_SEC_REGISTERED_INSIDER_SALES => [],
        ];
        return $arr;
    }
    public function getPath(): string {
        return $this->path = self::PATH_PS_DATA_CSV;
    }
    /**
     * @param $response
     * @return void
     */
    protected function responseToMeasurements($response): void {
        $start = $this->getScraper()->getStartDate();
        if($start){
            $startDate = substr($start, 2);
            $startDate = str_replace("-", "", $startDate)."	";
            $afterStart = QMStr::after($startDate, $response);
            if(!$afterStart){
                le("Nothing after $startDate");
            }
            $response = $startDate.$afterStart;
        }
        $lines = explode("\n", $response);
        $header = $lines[0];
        unset($lines[0]);
        foreach($lines as $i => $line){
            if(empty($line)){continue;}
            $cells = explode("\t", $line);
            if($cells[0] === "000000"){continue;}
            try {
                $at = $this->pluckStartAt($cells);
            } catch (\Throwable $e){
                QMLog::info("Skipping $line because: ".$e->getMessage());
                continue;
            }
            if($this->weShouldSkip($at)){continue;}
            $numberOfPurchases = $cells[1];
            $numberOfSells = $cells[2];
            $valueOfPurchases = $cells[3];
            $valueOfSells = $cells[4];
            $this->logInfo("$at: Parsing $line");
            try {
                $this->newMeasurement(self::NUMBER_OF_SEC_REGISTERED_INSIDER_PURCHASES, $at, $numberOfPurchases);
            } catch (DuplicateDataException $e){
                QMLog::info(__METHOD__.": ".$e->getMessage());
                continue;
            }
            $this->newMeasurement(self::NUMBER_OF_SEC_REGISTERED_INSIDER_SALES, $at, $numberOfSells);
            $this->newMeasurement(self::VALUE_OF_SEC_REGISTERED_INSIDER_PURCHASES, $at, $valueOfPurchases);
            $this->newMeasurement(self::VALUE_OF_SEC_REGISTERED_INSIDER_SALES, $at, $valueOfSells);
            $this->newMeasurement(self::RATIO_OF_VALUE_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES,
                $at, $valueOfPurchases / $valueOfSells);
            $this->newMeasurement(self::RATIO_OF_NUMBER_OF_SEC_REGISTERED_INSIDER_BUYS_TO_SALES, $at,
                $numberOfPurchases / $numberOfSells);
        }
    }
    /**
     * @param array $cells
     * @return string
     */
    protected function pluckStartAt(array $cells): string {
        $date = "20".$cells[0];
        $startAt = db_date(strtotime($date));
        return $startAt;
    }
    public function getParams(): array{
        return [];
    }

}
