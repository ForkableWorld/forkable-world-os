<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers;
use App\Exceptions\IncompatibleUnitException;
use App\Exceptions\InvalidFilePathException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\HasClassName;
use App\Traits\LoggerTrait;
use App\Files\FileHelper;
use OpenApi\Annotations\PathItem;
abstract class BasePath extends PathItem
{
    use LoggerTrait, HasClassName;
    /**
     * @var BaseScraper
     */
    protected $scraper;
    protected $newMeasurements = [];
    protected $variableName;
    protected $maximumFrequency = 86400;
    protected $exceptions;
    protected $variables;
    protected $variableDataByName;
    public function __construct(BaseScraper $scraper){
        parent::__construct([]);
        $this->scraper = $scraper;
    }
    abstract public function getAllVariablesData(): array;
    protected function getVariableDataByName(string $name):array{
        return $this->getAllVariablesData()[$name];
    }
    /**
     * @param $e
     */
    public function addException(\Throwable $e): void{
        $this->exceptions[] = $e;
    }
    /**
     * @return BaseScraper
     */
    public function getScraper(): BaseScraper{
        return $this->scraper;
    }
    /**
     * @param $response
     * @return void
     */
    abstract protected function responseToMeasurements($response): void;
    /**
     * @return int
     */
    public function getMaximumFrequency(): int{
        return $this->maximumFrequency;
    }
    /**
     * @param string $name
     * @param string $startAt
     * @param float $value
     * @return Measurement
     */
    protected function newMeasurement(string $name, string $startAt, float $value): ?Measurement {
        $s = $this->getScraper();
        if($s->getVariableName() && $s->getVariableName() !== $name){
            return null;
        }
        $uv = $this->getUserVariable($name);
        if($uv->alreadyHaveData($startAt)){
            $this->logDebug("Already have a $name measurement at $startAt");
            return null;
        }
        try {
            $m = $uv->newMeasurement([
                Measurement::FIELD_VALUE                   => $value,
                Measurement::FIELD_START_AT => $startAt,
                Measurement::FIELD_START_TIME => strtotime($startAt),
                Measurement::FIELD_SOURCE_NAME => $this->getScraper()->getNameAttribute(),
                Measurement::FIELD_CLIENT_ID => $this->getScraper()->getClientId(),
            ]);
            return $this->newMeasurements[$m->getVariableName()][$m->getStartAtAttribute()] = $m;
        } catch (IncompatibleUnitException | InvalidVariableValueException $e) {
            /** @var \LogicException $e */
            throw $e;
        }
    }
    protected function getVariable(string $name): Variable {
        if($v = $this->variables[$name] ?? null){return $v;} // Prevents repeat of attribute updates
        $v = Variable::findByName($name);
        if(!$v){$v = new Variable();}
        $v->is_public = 1;
        $v->name = $name;
        $v->creator_user_id = $this->getUserId();
        $v->client_id = $this->getClientId();
        $attributes = array_merge($this->getScraperVariableData(), $this->getVariableDataByName($name));
        $v->forceFill($attributes);
        try {
            $v->save();
        } catch (ModelValidationException $e) {
            le($e);
        }
        return $this->variables[$name] = $v;
    }
    public function getUserVariable(string $name): UserVariable {
        $v = $this->getVariable($name);
        return $v->getOrCreateUserVariable($this->getUserId(), [
            UserVariable::FIELD_CLIENT_ID => $this->getClientId()
        ]);
    }
    private function getClientId(): string {
        return $this->getScraper()->getClientId();
    }
    private function getUserId(): ?int {
        return $this->getScraper()->getUserId();
    }
    protected function saveMeasurements(): void{
        $measurements = $this->getNewMeasurementsByVariable();
        foreach($measurements as $variableName => $byDate){
            $uv = $this->getUserVariable($variableName);
            $uv->saveMeasurements($byDate);
            try {
                $uv->analyzeIfNecessary(__FUNCTION__);
            } catch (TooSlowToAnalyzeException $e) {
                le($e);
            }
        }
    }
    /**
     * @return array
     */
    public function getNewMeasurementsByVariable(): array {
        return $this->newMeasurements;
    }
    /**
     * @return array
     */
    public function getNewMeasurements():array{
        return Measurement::flatten($this->newMeasurements);
    }
    /**
     * @return void
     */
    public function scrape(): void {
        $this->newMeasurements = []; // Reset so we don't attempt to save twice
        $scraper = $this->getScraper();
        $response = $scraper->getRequest($this->getPath(), $this->getParams());
        $this->responseToMeasurements($response);
        //QMProfile::startProfile();
        try {
            $this->saveMeasurements();
        } catch (\Throwable $e){
            QMLog::info(__METHOD__.": ".$e->getMessage());
            $this->saveMeasurements();
        }
        //QMProfile::endProfileAndSaveResult();
    }
    abstract public function getPath():string;
    abstract public function getParams():array;
    private function getScraperVariableData(): array {
        return $this->getScraper()->getScraperVariableData();
    }
    /**
     * @return UserVariable[]
     */
    public function getUserVariables(): array {
        $pathVars = [];
        foreach($this->getAllVariablesData() as $name => $data){
            $pathVars[$name] = $this->getUserVariable($name);
        }
        return $pathVars;
    }
    public function getVariableNames(): array {
        return array_keys($this->getAllVariablesData());
    }
    protected function weShouldSkip(string $at): bool {
        $s = $this->getScraper();
        $startAt = $s->getStartAt();
        if(!$startAt){return false;}
        $endAt = $s->getEndAt();
        if(!$endAt){return false;}
        if($at > $endAt){
            return true;
        }
        if($at < $startAt){
            return true;
        }
        return false;
    }
    /**
     * @return string
     */
    public function variableName(): ?string {
        return $this->variableName;
    }
    /**
     * @param string $variableName
     */
    public function setVariableName(string $variableName): void{
        $this->variableName = $variableName;
    }
    public function getUrl(array $query = [], array $pathParams = []):string{
        return $this->getScraper()->getUrlForPath($this->getPath(), $query, $pathParams);
    }
    /**
     * @param array $query
     * @param array $pathParams
     * @return string
     * @throws \App\Exceptions\InvalidFilePathException
     */
    public function getFilePath(array $query = [], array $pathParams = []): string {
        $url = $this->getUrl($query, $pathParams);
        return $this->getScraper()->getResponseFilePath($url);
    }
    public function lastScrapedAt(array $query = [], array $pathParams = []):?string{
        try {
            $filepath = $this->getFilePath($query, $pathParams);
            return date_or_null(FileHelper::getLastModifiedTime($filepath));
        } catch (InvalidFilePathException $e) {
            le($e);
            throw new \LogicException();
        }
    }
    public function scrapedSince($timeAt, array $query = [], array $pathParams = []): bool {
        $modified = $this->lastScrapedAt($query, $pathParams);
        if(!$modified){return false;}
        return time_or_exception($timeAt) > time_or_exception($modified);
    }
    public function needToScrape(array $query = [], array $pathParams = []): bool {
        $needTo = !$this->scrapedSince(time() - $this->getMaximumFrequency(), $query, $pathParams);
        if(!$needTo){
            $this->logInfo("Too soon to scrape again yet");
        }
        return $needTo;
    }
    protected function getStartAt(): ?string {
        return $this->getScraper()->getStartAt();
    }
    protected function getEndAt(): ?string {
        return $this->getScraper()->getEndAt();
    }
    protected function tooEarly(string $at): bool {
        $start = $this->getStartAt();
        if(!$start){return false;}
        return $start > $at;
    }
    protected function tooLate(string $at): bool {
        $end = $this->getEndAt();
        if(!$end){return false;}
        return $end < $at;
    }
    public function __toString() {
        return (new \ReflectionClass(static::class))->getShortName()." for variable ".$this->variableName;
    }
}
