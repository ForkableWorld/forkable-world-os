<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\WpPost;
use App\Models\BaseModel;
use App\Models\WpPost;
use App\Traits\PropertyTraits\WpPostProperty;
use App\Properties\Base\BasePostContentProperty;
class WpPostPostContentProperty extends BasePostContentProperty
{
    use WpPostProperty;
    public $table = WpPost::TABLE;
    public $parentClass = WpPost::class;
    public static function fixTooBig(): array{
        return self::fixTooLong();
    }
    public static function fixTooLong(): array{
        WpPostRecordSizeInKbProperty::updateAll();
        return parent::fixTooLong();
    }
    public static function handleTooLong(int $id): BaseModel{
        $post = static::findParent($id);
        $kb = round(strlen($post->post_content)/1024);
        $post->logInfo("Deleting because $kb KB...");
        $post->forceDelete();
        return $post;
    }
    /**
     * @param int $id
     * @return WpPost
     */
    public static function findParent($id): ?BaseModel{
        return parent::findParent($id);
    }
}
