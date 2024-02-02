<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\DataSources\Connectors\Exceptions\RecentImportException;
use App\DataSources\QMDataSource;
use App\Exceptions\NoGeoDataException;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Models\Connection;
use App\Astral\ConnectionBaseAstralResource;
use App\Models\User;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\Properties\User\UserIdProperty;
use App\Storage\DB\QMQB;
use App\Types\TimeHelper;
use Illuminate\Database\Eloquent\Builder;
use App\Actions\Actionable;
use Illuminate\Support\Collection;
use LogicException;
use Tests\TestGenerators\ImportTestFiles;
use Throwable;
trait ImportableTrait {
	use TestableTrait, Actionable;
	/**
	 * @return Connection[]
	 */
	public static function importWaitingStaleStuck(): array{
		$connections = [];
		$connections = array_merge($connections, static::importWaiting());
		$connections = array_merge($connections, static::importNeverImported());
		$connections = array_merge($connections, static::importStale());
		$connections = array_merge($connections, static::importStuck());
		return $connections;
	}
	/**
	 * @param string $message
	 */
	public function slack(string $message){
		JobTestCase::slack("$this: \n" . $message);
	}
	/**
	 * @return string
	 */
	public function __toString(){
		return $this->getNameAttribute();
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Builder|QMQB
	 */
	abstract public static function whereStale();
	/**
	 * @return \Illuminate\Database\Eloquent\Builder|QMQB
	 */
	abstract public static function whereWaiting(): Builder|QMQB;
	/**
	 * @return \Illuminate\Database\Eloquent\Builder|QMQB
	 */
	public static function whereNeverImported(){
		return static::whereNull(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT);
	}
	/**
	 * @param Builder|null $qb
	 * @return \Illuminate\Database\Eloquent\Builder|QMQB
	 */
	abstract public static function whereStuck(Builder $qb = null);
	/**
	 * @param \Illuminate\Database\Eloquent\Builder|QMQB $qb
	 */
	public static function addImportedStartedClause($qb){
		$qb->where(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT, "<",
			db_date(time() - static::SECONDS_BETWEEN_IMPORTS));
	}
	/**
	 * @return Connection[]
	 */
	public static function importStale(): array{
		return static::importByQuery(self::whereStale(),
			"STALE: " . static::FIELD_IMPORT_ENDED_AT . " before more than " .
			TimeHelper::convertSecondsToHumanString(static::SECONDS_BETWEEN_IMPORTS) . " ago");
	}
	/**
	 * @return Connection[]
	 */
	public static function importNeverImported(): array{
		return static::importByQuery(static::whereNeverImported(),
			"NEVER IMPORTED: " . static::FIELD_IMPORT_STARTED_AT . " is null");
	}
	/**
	 * @return Connection[]
	 */
	public static function importStuck(): array{
		return static::importByQuery(self::whereStuck(),
			"STUCK: " . static::FIELD_IMPORT_STARTED_AT . " more than a day ago and never ended");
	}
	/**
	 * @return Connection[]
	 */
	public static function importWaiting(): array{
		$qb = self::whereWaiting();
		// WHY? static::addImportedStartedClause($qb);  //This prevents us from getting null import started ats
		return static::importByQuery($qb, "status is WAITING");
	}
	public static function importJobsTest(){
		JobTestCase::setMaximumJobDuration(0.00001);
		JobTestCase::resetStartTime();
		static::importWaiting();
		JobTestCase::resetStartTime();
		static::importNeverImported();
		JobTestCase::resetStartTime();
		static::importStale();
		JobTestCase::resetStartTime();
		static::importStuck();
	}
	/**
	 * @return \Illuminate\Database\Eloquent\Builder|QMQB
	 */
	public static function whereErrored(){
		$qb = static::whereNotNull(static::TABLE . '.' . static::FIELD_INTERNAL_ERROR_MESSAGE);
		static::excludeNonApiUpdateAndDisabledConnectors($qb);
		static::excludeTestAndDeletedUsers($qb);
		static::orderByImportStartedAt($qb);
		return $qb;
	}
	/**
	 * @param \Illuminate\Database\Eloquent\Builder|QMQB $qb
	 */
	private static function orderByImportStartedAt(Builder $qb){
		$qb->orderBy(static::TABLE . '.' . static::FIELD_IMPORT_STARTED_AT, 'asc');
	}
	/**
	 * @return Collection|Connection[]
	 */
	public static function logErrorsFromLast24(): Collection|array{
		$qb = static::whereErrored();
		$qb->where(static::TABLE . '.' . static::FIELD_UPDATED_AT, ">", db_date(time() - 86400));
		$connections = $qb->get();
		if(!$connections->count()){
			\App\Logging\ConsoleLog::info("=== No ERRORED connections to log ===");
			return [];
		}
		QMLog::table($connections, "=== ERRORS ===");
		return $connections;
	}
	/**
	 * @return Collection|Connection[]
	 */
	public static function logStuck(): Collection{
		$qb = self::whereStuck();
		$connections = $qb->get();
		if(!$connections->count()){
			ConsoleLog::info("=== No STUCK connections to log ===");
			return [];
		}
		QMLog::logLink(Connection::getDataLabIndexUrl(), "=== STUCK ===");
		QMLog::table($connections, "=== STUCK ===");
		return $connections;
	}
	/**
	 * @param string $fieldName
	 * @return string
	 */
	public static function fieldString(string $fieldName): string{
		return static::TABLE . '.' . $fieldName;
	}

    /**
     * @param string|null $reason
     * @return void
     * @throws NoGeoDataException
     * @throws ConnectorDisabledException
     */
    abstract public function import(string $reason = null): void;
	abstract public function getDataSourceId(): int;
	/**
	 * @return string
	 */
	public function getPHPUnitTestUrl(): string{
		SolutionButton::reset();
		$userId = $this->getUserId();
		$name = $this->getDataSourceDisplayName();
		$shortClass = (new \ReflectionClass(static::class))->getShortName();
		$functions = $shortClass . '::find(' . $this->getId() . ')->test();';
		$testName = $shortClass . "User" . $userId . 'Source' . str_replace(' ', '', $name);
		$url = ImportTestFiles::getUrl($testName, $functions, __CLASS__);
		QMLog::logLink($url . " \n", "$this PHPUnit Test"); // Keeps running adjacent to INFO in console logs
		return $url;
	}
	/**
	 * @return QMDataSource
	 */
	abstract public function getQMDataSource(): ?QMDataSource;
	/**
	 * @throws ConnectorDisabledException
	 * @throws NoGeoDataException
	 */
	public function test(): void{
		$this->import(__FUNCTION__);
	}
	/**
	 * @param \Illuminate\Database\Eloquent\Builder|QMQB $qb
	 * @param string $reason
	 * @return static[]
	 */
	public static function importByQuery($qb, string $reason): array{
		if(static::class === Connection::class){
			Connection::excludeNonApiUpdateAndDisabledConnectors($qb);
		}
        //$qb->where(static::TABLE . '.' . static::FIELD_USER_ID, 93394);
		$qb->whereNotIn(static::TABLE . '.' . static::FIELD_USER_ID, UserIdProperty::getTestSystemAndDeletedUserIds());
		$qb->whereNull(static::TABLE . '.' . static::FIELD_DELETED_AT);
        $qb->where(static::TABLE . '.' . static::FIELD_CONNECT_STATUS, '=',
            ConnectionConnectStatusProperty::CONNECT_STATUS_CONNECTED);
		$requests = [];
//		$sql = $qb->getSimpleSQL();
//		QMLog::info($sql);
		while($before = $qb->count()){
			$message = "$before " . static::TABLE . " where $reason";
			ConsoleLog::info($message);
			/** @var static $model */
			$model = $qb->first();
            $connector = $model->connector;
            /** @var User $user */
            $user = $model->getUser();
            try {
                if (!$connector->available_outside_us && $user->isOutsideUS()) {
                    $geo = $user->getIpGeoLocation()->print();
                    $model->disconnect("$connector->name not available in $geo",
                        "Sorry! $connector->display_name import is not currently available in your location!");
                    continue;
                }
            } catch (NoGeoDataException $e) {
                $model->disconnect("$connector->name not available in " . $e->getMessage());
                continue;
            }
            if(collect($requests)->firstWhere('id', $model->getId())){
				le("Why are we importing twice for connection: " . $model . "?");
			}
			$requests[] = $model;
			try {
				$model->getPHPUnitTestUrl();
				$model->import($reason);
			} catch (RecentImportException|ConnectorDisabledException $e) {
				QMLog::error(__METHOD__.": ".$e->getMessage());
            } catch (Throwable $e) {
				if(stripos($e->getMessage(), "can't refresh tokens on staging") !== false){
					$model->logInfo(__METHOD__.": ".$e->getMessage());
					continue;
				}
				/** @var LogicException $e */
				throw $e;
			}
			if(JobTestCase::jobDurationExceedsTimeLimit()){
				break;
			}
		}
		return $requests;
	}
}
