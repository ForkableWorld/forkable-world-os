<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\Connector;
use App\Astral\ConnectorBaseAstralResource;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Fields\BelongsTo;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\QMDataSource;
class BaseConnectorIdProperty extends BaseIntegerIdProperty{
    use ForeignKeyIdTrait;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The id for the connector data source from which the measurement was obtained';
	public $example = FitbitConnector::ID;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CONNECTOR;
	public $htmlType = 'text';
	public $image = ImageUrls::CONNECTION;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'connector_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|integer|min:1|max:2147483647';
	public $title = 'Connector';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|integer|min:1|max:2147483647';
	public const NAME_SYNONYMS = ['connector_name'];
    /**
     * @return Connector
     */
    public static function getForeignClass(): string{
        return Connector::class;
    }
    public static function applyRequestParamsToQuery(\Illuminate\Database\Query\Builder $qb): void{
        parent::applyRequestParamsToQuery($qb);
    }
    /**
     * @param string $name
     * @return QMDataSource
     */
    public static function findByName(string $name): QMDataSource{
        return QMDataSource::find($name);
    }
    public static function belongsTo(): BelongsTo {
        return ConnectorBaseAstralResource::belongsTo("Connector", "connector")->hideFromIndex();
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
