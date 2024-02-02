<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Base\BaseMeasurementExport;
use App\Traits\HasErrors;
use App\Traits\HasModel\HasUser;
use App\UI\FontAwesome;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
/**
 * App\Models\MeasurementExport
 * @OA\Schema (
 *      definition="MeasurementExport",
 *      required={"variable_id", "source_name, "start_time", "value", "unit_id"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="ID of User",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          description="Status of Measurement Export",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="error_message",
 *          description="Error message",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="When the record was first created. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="When the record in the database was last updated. Use ISO 8601 datetime format",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 * @property integer $id
 * @property integer $user_id
 * @property string $client_id
 * @property string $status
 * @property string $type
 * @property string $output_type
 * @property string $error_message
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @method static \Illuminate\Database\Query\Builder|MeasurementExport whereId($value)
 * @method static \Illuminate\Database\Query\Builder|MeasurementExport whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|MeasurementExport whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|MeasurementExport whereErrorMessage($value)
 * @method static \Illuminate\Database\Query\Builder|MeasurementExport whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|MeasurementExport whereUpdatedAt($value)
 * @property string|null $deleted_at
 * @method static Builder|MeasurementExport newModelQuery()
 * @method static Builder|MeasurementExport newQuery()
 * @method static Builder|MeasurementExport query()
 * @method static Builder|MeasurementExport whereClientId($value)
 * @method static Builder|MeasurementExport whereDeletedAt($value)
 * @method static Builder|MeasurementExport whereOutputType($value)
 * @method static Builder|MeasurementExport whereType($value)
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property-read OAClient|null $client
 */
class MeasurementExport extends BaseMeasurementExport {
    use HasFactory;

	use HasUser;
	public const CLASS_DESCRIPTION = "A request from a user to export their data as a spreadsheet. ";
	const CLASS_CATEGORY = Measurement::CLASS_CATEGORY;
	use HasErrors;
	public const FONT_AWESOME = FontAwesome::FILE_EXPORT_SOLID;
	const STATUS_WAITING = 'WAITING';
	const STATUS_FULFILLED = 'FULFILLED';
	const STATUS_ALREADY_EMAILED = 'ALREADY_EMAILED';
	const STATUS_ERROR = 'ERROR';
	const STATUS_EXPORTING = 'EXPORTING';
	const STATUS_NO_EMAIL = 'NO_EMAIL';
	const STATUS_NO_USER = 'NO_USER';
	const STATUS_NO_MEASUREMENTS = 'NO_MEASUREMENTS';
	private $allowedStatuses = [
		self::STATUS_WAITING,
		self::STATUS_FULFILLED,
		self::STATUS_NO_MEASUREMENTS,
		self::STATUS_NO_USER,
		self::STATUS_ERROR,
		self::STATUS_EXPORTING,
		self::STATUS_NO_EMAIL,
		self::STATUS_ALREADY_EMAILED,
	];
	protected array $rules = [
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
		self::FIELD_STATUS => 'required|max:32',
		self::FIELD_TYPE => 'required',
		self::FIELD_OUTPUT_TYPE => 'required',
		self::FIELD_ERROR_MESSAGE => 'nullable|max:255',
	];
	/**
	 * @param $value
	 */
	public function setStatusAttribute($value){
		if(!in_array($value, $this->allowedStatuses)){
			throw new InvalidArgumentException("Invalid status value $value for measurement import.  Allowed values: " .
				implode(', ', $this->allowedStatuses));
		}
		$this->attributes['status'] = $value;
	}
	/**
	 * @param $value
	 */
	public function setErrorMessageAttribute($value){
		$this->attributes['error_message'] = $value;
	}
	public static function setExportingAndErroredExportsToWaiting(){
		MeasurementExport::where('status', MeasurementExport::STATUS_ERROR)
			->update(['status' => MeasurementExport::STATUS_WAITING]);
		MeasurementExport::where('status', MeasurementExport::STATUS_EXPORTING)
			->update(['status' => MeasurementExport::STATUS_WAITING]);
		MeasurementExport::where('user_id', 0)->delete();
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
