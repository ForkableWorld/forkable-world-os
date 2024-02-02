<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Services;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\BadRequestException;
use App\Models\Measurement;
use App\Models\MeasurementExport;
use App\Models\User;
use App\Storage\QueryBuilderHelper;
use Illuminate\Database\Connection;
use Illuminate\Log\Logger;
use Illuminate\Mail\Mailer;
use DB;
class MeasurementService extends BaseService {
    public $measurementExportRequest;
    public $userId;
    /** @var Mailer */
    private $mailer;
    /**
     * MeasurementService constructor.
     * @param Connection $connection
     * @param Logger $logger
     * @param Mailer $mailer
     */
    public function __construct(Connection $connection, Logger $logger, Mailer $mailer){
        parent::__construct($connection, $logger);
        $this->mailer = $mailer;
    }
    /**
     * @param array $filters
     * @return Collection|Measurement[]
     */
    public function all($filters = []){
        $query = Measurement::with([
            'variable',
            'connector',
            'source',
            'unit',
            'originalUnit'
        ]);
        QueryBuilderHelper::addParams($query->getQuery(), $filters);
        return $query->get();
    }
    /**
     * @param int $id
     * @param array $columns
     * @return null|Measurement
     */
    public function find($id, $columns = ['*']){
        return Measurement::find($id, $columns);
    }
    /** @noinspection PhpDocMissingThrowsInspection */
    /**
     * @param int $id
     * @return bool|null
     */
    public function delete($id){
        if(!($measurement = $this->find($id))){
            return false;
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        return $measurement->delete();
    }
    /**
     * @param User $user
     * @param string $type
     * @param null $clientId
     * @param string $output
     * @return int
     * @throws BadRequestException
     */
    public function createExportRequestRecord($user, $type = 'user', $clientId = null, $output = 'csv'){
        if(!$user->user_email){throw new BadRequestException('You must provide email address before exporting');}
        $exportQuery = MeasurementExport::where('user_id', $user->getId())
            ->where('status', MeasurementExport::STATUS_WAITING)
            ->where('type', $type);
        if(!empty($clientId)){
            $exportQuery->where('client_id', $clientId);
        }
        $this->measurementExportRequest = $exportQuery->first();
        if(!empty($this->measurementExportRequest)){
            throw new BadRequestException('You already have a pending measurement export request!');
        }
        $this->measurementExportRequest = new MeasurementExport();
        $id = DB::table('measurement_exports')->insertGetId([
            'status'      => MeasurementExport::STATUS_WAITING,
            'type'        => $type,
            'output_type' => $output,
            'client_id'   => $clientId,
            'user_id'     => $user->getId()
        ]);
        return $id;
    }
    /**
     * @param $userId
     * @return mixed|null
     */
    public function getLatestMoodMeasurement($userId){
        $variableName = 'Overall Mood';
        $images = [
            1 => 'ic_mood_depressed.png',
            2 => 'ic_mood_sad.png',
            3 => 'ic_mood_ok.png',
            4 => 'ic_mood_happy.png',
            5 => 'ic_mood_ecstatic.png'
        ];
        $measurement = DB::table('measurements')
            ->join('variables', 'variables.id', '=', 'measurements.variable_id')
            ->select('measurements.value')
            ->where('variables.name', $variableName)
            ->where('measurements.user_id', $userId)
            ->orderBy('measurements.start_time', 'desc')
            ->first();
        $latestMood = null;
        if(!empty($measurement)){
            $latestMood = $images[$measurement->value];
        }
        return $latestMood;
    }
    /**
     * @param $userId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getLatestMeasurements($userId, $limit = 4){
        $measurements = DB::table('user_variables')
            ->join('variables', 'variables.id', '=', 'user_variables.variable_id')
            ->join('units', 'units.id', '=', 'user_variables.last_unit_id')
            ->select('user_variables.last_value AS value', 'variables.name AS variableName', 'units.abbreviated_name AS unitAbbreviatedName')
            ->where('user_variables.user_id', $userId)
            ->orderBy('user_variables.updated_at', 'desc')
            ->limit($limit)
            ->get();
        return $measurements;
    }
}
