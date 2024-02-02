<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Scrapers;
use App\Models\OAClient;
use App\Models\User;
use App\Models\UserVariable;
use App\Properties\User\UserIdProperty;
use App\Traits\HasClassName;
use App\Traits\HasModel\HasUser;
use App\Traits\LoggerTrait;
use App\Traits\Scrapes;
use App\Types\TimeHelper;
abstract class BaseScraper
{
    use LoggerTrait, Scrapes, HasUser, HasClassName;
    const RESPONSE_TYPE_CSV = "csv";
    const RESPONSE_TYPE_JSON = "json";
    protected $useFileResponsesInTesting = true;
    protected $startAt;
    protected $endAt;
    protected $variableName;
    abstract public function getUserLogin(): string;
    public function getUser(): User {
        $login = $this->getUserLogin();
        $u = User::findByLoginName($login);
        if($u){return $u;}
        $email = $login.'@quantimo.do';
        $u = User::firstOrCreate([User::FIELD_USER_EMAIL => $email], [
            User::FIELD_USER_LOGIN => $login,
            User::FIELD_USER_PASS => $login,
            User::FIELD_USER_EMAIL => $email,
            User::FIELD_CLIENT_ID => $this->getClientId(),
        ]);
        return $u;
    }
    abstract public function getBaseApiUrl():string;
    /**
     * @return BasePath[]
     */
    abstract public function getPathsClasses():array;
    /**
     * @return BasePath[]
     */
    public function getPaths(): array{
        if($this->paths){return $this->paths;}
        $paths = [];
        $classes = $this->getPathsClasses();
        foreach($classes as $class){
            $paths[] = new $class($this);
        }
        return $paths;
    }
    /**
     * @return void
     */
    public function scrape(): void {
        $paths = $this->getPaths();
        foreach($paths as $path){
            $this->currentPath = $path;
            if($path->needToScrape()){
                $path->scrape();
            }
        }
    }
    /**
     * @param int|string $startTimeAt
     * @param int|string $endTimeAt
     * @param string|null $variableName
     * @return static
     */
    public static function scrapeBetween($startTimeAt, $endTimeAt, string $variableName = null): self {
        $s = new static();
        $s->setStartAt($startTimeAt);
        $s->setEndAt($endTimeAt);
        if($variableName){
            $s->setVariableName($variableName);
        }
        $s->scrape();
        return $s;
    }
    abstract public function getScraperVariableData(): array;
	/**
	 * @param $key
	 * @return null
	 */
	public function getAttribute($key){
        return $this->$key ?? null;
    }
    public function getNameAttribute():string{
        return (new \ReflectionClass(static::class))->getShortName();
    }
    public function getClient(): OAClient {
        $clientId = $this->getSlugifiedClassName();
        $client = OAClient::firstOrCreate([
            OAClient::FIELD_CLIENT_ID => $clientId
        ], [
            OAClient::FIELD_CLIENT_ID => $clientId,
            OAClient::FIELD_USER_ID => UserIdProperty::USER_ID_SYSTEM,
        ]);
        return $client;
    }
    public function getClientId(): string {
        return $this->getClient()->client_id;
    }
    /**
     * @return UserVariable[]
     */
    public function getUserVariables(): array {
        $userVariables = [];
        foreach($this->getPaths() as $path){
            $pathVars = $path->getUserVariables();
            foreach($pathVars as $pathVar){
                $userVariables[$pathVar->getVariableName()] = $pathVar;
            }
        }
        return $userVariables;
    }
    public static function logUserVariableUrls(){
        $me = new static();
        $userVariables = $me->getUserVariables();
        foreach($userVariables as $v){
            $v->logInfo($v->getUrl());
        }
    }
    public function getVariableNames(): array{
        $paths = $this->getPaths();
        $names = [];
        foreach($paths as $path){
            $names = array_merge($names, $path->getVariableNames());
        }
        sort($names);
        return $names;
    }
    public function getStartAt(): ?string {
        return $this->startAt;
    }
    /**
     * @param string|int $startTimeAt
     */
    public function setStartAt($startTimeAt): void{
        $this->startAt = db_date($startTimeAt);
    }
    public function getEndAt(): ?string {
        return $this->endAt;
    }
    public function getEndDate(): ?string {
        if(!$this->endAt){return null;}
        return TimeHelper::YYYYmmddd($this->endAt);
    }
    public function getStartDate(): ?string {
        if(!$this->startAt){return null;}
        return TimeHelper::YYYYmmddd($this->startAt);
    }
    /**
     * @param string|int $endTimeAt
     */
    public function setEndAt($endTimeAt): void{
        $this->endAt = db_date($endTimeAt);
    }
    /**
     * @return string
     */
    public function getVariableName(): ?string {
        return $this->variableName;
    }
    /**
     * @param string $variableName
     */
    public function setVariableName(string $variableName): void{
        $this->variableName = $variableName;
    }
    public function getUserId(): ?int{
        return $this->getUser()->getId();
    }
	/**
	 * @return string
	 */
	public function __toString() {
        return (new \ReflectionClass(static::class))->getShortName()." for variable: ".$this->variableName;
    }
}
