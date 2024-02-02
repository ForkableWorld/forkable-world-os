<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers\FederalReserve;
use App\Exceptions\InvalidResponseDataException;
use App\Units\IndexUnit;
class ObservationsPath extends SeriesPath {
    public function getPath(): string{
        return "series/observations";
    }
    public function at($row): string{
        $date = $row->date;
        $at = db_date($date);
        return $at;
    }
    public function scrape(): void{
        if($this->seriesId){
            parent::scrape();
            return;
        }
        $ids = $this->getSeriesIds();
        foreach($ids as $id){
            $this->setSeriesId($id);
            parent::scrape();
        }
    }
    /**
     * @param $response
     */
    protected function responseToMeasurements($response): void{
	    if(is_string($response)){$response = json_decode($response);}
        foreach($response->observations as $row){
            try {
                $at = $this->at($row);
                if($this->tooEarly($at)){continue;}
                if($this->tooLate($at)){break;}
                $this->newMeasurement($this->variableName(), $at, $this->value($row));
            } catch (InvalidResponseDataException $e) {
                $this->logInfo($e->getMessage(), ['row' => $row]);
                $this->addException($e);
                continue;
            }
        }
    }
    /**
     * @param $row
     * @return float
     * @throws InvalidResponseDataException
     */
    public function value($row): float{
        $val = $row->value;
        if(!is_numeric($val)){
            throw new InvalidResponseDataException("value $val is not valid",
                $this->getUserVariable($this->variableName()));
        }
        return $val;
    }
    public function unitId(): int{
        return IndexUnit::ID;
    }
}
