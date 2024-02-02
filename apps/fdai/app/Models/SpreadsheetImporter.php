<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Models;
use App\Models\Base\BaseSpreadsheetImporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
/**
 * App\Models\SpreadsheetImporter
 * @property int $id Spreadsheet Importer ID number
 * @property string $name Lowercase system name for the data source
 * @property string $display_name Pretty display name for the data source
 * @property string $image URL to the image of the Spreadsheet Importer logo
 * @property string|null $get_it_url URL to a site where one can get this device or application
 * @property string $short_description Short description of the service (such as the categories it tracks)
 * @property string $long_description Longer paragraph description of the data provider
 * @property bool $enabled Set to 1 if the Spreadsheet Importer should be returned when listing Spreadsheet Importers
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $client_id
 * @property Carbon|null $deleted_at
 * @property int|null $wp_post_id
 * @property int|null $number_of_measurement_imports Number of Spreadsheet Import Requests for this Spreadsheet
 *     Importer.
 *                             [Formula:
 *                                 update spreadsheet_importers
 *                                     left join (
 *                                         select count(id) as total, spreadsheet_importer_id
 *                                         from spreadsheet_importer_requests
 *                                         group by spreadsheet_importer_id
 *                                     )
 *                                     as grouped on spreadsheet_importers.id = grouped.spreadsheet_importer_id
 *                                 set spreadsheet_importers.number_of_spreadsheet_importer_requests =
 *     count(grouped.total)
 *                             ]
 * @property int|null $number_of_measurements Number of Measurements for this Spreadsheet Importer.
 *                                 [Formula: update spreadsheet_importers
 *                                     left join (
 *                                         select count(id) as total, spreadsheet_importer_id
 *                                         from measurements
 *                                         group by spreadsheet_importer_id
 *                                     )
 *                                     as grouped on spreadsheet_importers.id = grouped.spreadsheet_importer_id
 *                                 set spreadsheet_importers.number_of_measurements = count(grouped.total)]
 * @property-read OAClient|null $oa_client

 * @property-read WpPost|null $wp_post
 * @method static Builder|BaseModel applyRequestParams($request)
 * @method static Builder|BaseModel exclude($columns)
 * @method static Builder|BaseModel excludeLargeColumns()
 * @method static Builder|BaseModel nPerGroup($group, $n = 10)
 * @method static Builder|SpreadsheetImporter newModelQuery()
 * @method static Builder|SpreadsheetImporter newQuery()
 * @method static Builder|SpreadsheetImporter query()
 * @method static Builder|SpreadsheetImporter whereClientId($value)
 * @method static Builder|SpreadsheetImporter whereCreatedAt($value)
 * @method static Builder|SpreadsheetImporter whereDeletedAt($value)
 * @method static Builder|SpreadsheetImporter whereDisplayName($value)
 * @method static Builder|SpreadsheetImporter whereEnabled($value)
 * @method static Builder|SpreadsheetImporter whereGetItUrl($value)
 * @method static Builder|SpreadsheetImporter whereId($value)
 * @method static Builder|SpreadsheetImporter whereImage($value)
 * @method static Builder|SpreadsheetImporter whereLongDescription($value)
 * @method static Builder|SpreadsheetImporter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpreadsheetImporter
 *     whereNumberOfMeasurementImports($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\SpreadsheetImporter
 *     whereNumberOfMeasurements($value)
 * @method static Builder|SpreadsheetImporter whereShortDescription($value)
 * @method static Builder|SpreadsheetImporter whereUpdatedAt($value)
 * @method static Builder|SpreadsheetImporter whereWpPostId($value)
 * @mixin \Eloquent
 * @property mixed $raw
 * @property int $sort_order
 * @method static Builder|SpreadsheetImporter whereSortOrder($value)
 * @property-read OAClient|null $client
 */
class SpreadsheetImporter extends BaseSpreadsheetImporter {
	const CLASS_CATEGORY = Connection::CLASS_CATEGORY;

}
