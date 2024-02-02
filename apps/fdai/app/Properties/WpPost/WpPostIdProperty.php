<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\WpPost;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\WpPost;
use App\Traits\PropertyTraits\WpPostProperty;
use App\Properties\Base\BaseIntegerIdProperty;
use App\Fields\Field;
class WpPostIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use WpPostProperty;
    public $table = WpPost::TABLE;
    public const NAME = WpPost::FIELD_ID;
    public $name = self::NAME;
    public $parentClass = WpPost::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'wp_post_id',
        'id',
    ];
}
