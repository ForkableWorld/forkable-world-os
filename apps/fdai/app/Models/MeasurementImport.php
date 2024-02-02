<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Buttons\RelationshipButtons\MeasurementImport\MeasurementImportUserButton;
use App\Buttons\RelationshipButtons\RelationshipButton;
use App\DataSources\SpreadsheetImportRequest;
use App\Exceptions\ModelValidationException;
use App\Models\Base\BaseMeasurementImport;
use App\Storage\DB\QMQB;
use App\Traits\HasDBModel;
use App\Traits\HasErrors;
use App\Traits\HasModel\HasDataSource;
use App\Traits\HasModel\HasImporterConnection;
use App\Traits\HasModel\HasUser;
use App\Traits\ImportableTrait;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use App\Actions\ActionEvent;
/**
 * App\Models\MeasurementImport
 * @property integer $user_id
 * @property string $source_name
 * @property string $file
 * @method static \Illuminate\Database\Query\Builder|MeasurementImport whereUserId($value)
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $status
 * @property string $error_message
 * @property string|null $deleted_at
 * @property string|null $client_id
 * @property-read User $user
 * @method static Builder|MeasurementImport newModelQuery()
 * @method static Builder|MeasurementImport newQuery()
 * @method static Builder|MeasurementImport query()
 * @method static Builder|MeasurementImport whereClientId($value)
 * @method static Builder|MeasurementImport whereCreatedAt($value)
 * @method static Builder|MeasurementImport whereDeletedAt($value)
 * @method static Builder|MeasurementImport whereErrorMessage($value)
 * @method static Builder|MeasurementImport whereFile($value)
 * @method static Builder|MeasurementImport whereId($value)
 * @method static Builder|MeasurementImport whereSourceId($value)
 * @method static Builder|MeasurementImport whereSourceName($value)
 * @method static Builder|MeasurementImport whereStatus($value)
 * @method static Builder|MeasurementImport whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read OAClient|null $oa_client
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @property Carbon|null $import_started_at
 * @property Carbon|null $import_ended_at
 * @property string|null $reason_for_import
 * @property string|null $user_error_message
 * @property string|null $internal_error_message

 * @method static Builder|MeasurementImport whereImportEndedAt($value)
 * @method static Builder|MeasurementImport whereImportStartedAt($value)
 * @method static Builder|MeasurementImport whereInternalErrorMessage($value)
 * @method static Builder|MeasurementImport whereReasonForImport($value)
 * @method static Builder|MeasurementImport whereUserErrorMessage($value)
 * @property-read Collection|ActionEvent[] $actions
 * @property-read int|null $actions_count
 * @property mixed $raw
 * @property-read OAClient|null $client
 */
class MeasurementImport extends BaseMeasurementImport {
    use HasFactory;

	use HasErrors, HasUser, HasDataSource, HasImporterConnection, ImportableTrait, HasDBModel;
	public static function getSlimClass(): string{ return SpreadsheetImportRequest::class; }
	public const CLASS_DESCRIPTION = "An uploaded spreadsheet or data file to be imported. ";
	public const FONT_AWESOME = FontAwesome::FILE_IMPORT_SOLID;
	public const DEFAULT_IMAGE = ImageUrls::ESSENTIAL_COLLECTION_UPLOAD;
	public const CLASS_CATEGORY = ConnectorImport::CLASS_CATEGORY;
	protected array $rules = [
		self::FIELD_USER_ID => 'required|numeric|min:1',
		self::FIELD_FILE => 'required|max:255',
		self::FIELD_STATUS => 'required|max:25',
		self::FIELD_ERROR_MESSAGE => 'nullable|max:65535',
		self::FIELD_SOURCE_NAME => 'nullable|max:80',
		self::FIELD_CLIENT_ID => 'nullable|max:80',
	];
	/**
	 * @param array $options
	 * @return bool
	 * @throws ModelValidationException
	 */
	public function save(array $options = []): bool{
		$res = parent::save($options);
		return $res;
	}
	/**
	 * @inheritDoc
	 */
	public static function whereStale(): QMQB{
		return SpreadsheetImportRequest::whereStale();
	}
	/**
	 * @inheritDoc
	 */
	public static function whereWaiting(): QMQB{
		return SpreadsheetImportRequest::whereWaiting();
	}
	/**
	 * @inheritDoc
	 */
	public static function whereStuck(Builder $qb = null): QMQB{
		return SpreadsheetImportRequest::whereStuck($qb);
	}
    /**
     * @param \Illuminate\Database\Eloquent\Builder|QMQB $qb
     * @param string $reason
     * @return static[]
     */
	public static function importByQuery($qb, string $reason): array{
		return SpreadsheetImportRequest::importByQuery($qb, $reason, false);
	}
	public function import(string $reason = null): void{
		$this->getDBModel()->import($reason);
	}
	public function getDataSourceId(): int{
		return $this->getQMDataSource()->getId();
	}
	/**
	 * @return RelationshipButton[]
	 */
	public function getInterestingRelationshipButtons(): array{
		return [
			new MeasurementImportUserButton($this),
		];
	}
	public function getUserId(): ?int{
		return $this->attributes[self::FIELD_USER_ID];
	}
}
