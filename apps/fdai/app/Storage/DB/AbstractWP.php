<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Storage\DB;
use App\Models\WpPost;
use App\Models\WpPostmetum;
class AbstractWP extends QMDB
{
    public static function posts(){
        return static::getBuilderByTable(WpPost::TABLE);
    }
    public static function postMeta(){
        return static::getBuilderByTable(WpPostmetum::TABLE);
    }
    public static function getPostsIndexedByType(): array {
        $posts = static::getPosts();
        $byType = [];
        foreach($posts as $post){
            $byType[$post->post_type][$post->post_title][] = $post;
        }
        return $byType;
    }
    /**
     * @return \Illuminate\Support\Collection|WpPost[]
     */
    public static function getPosts(){
        $posts = static::getAllFromTable(WpPost::TABLE);
        $meta = static::getPostMeta();
        /** @var WpPost $post */
        foreach($posts as $post){
            $post->post_meta = $meta[$post->ID] ?? [];
        }
        return $posts;
    }
    /**
     * @return array
     */
    public static function getPostMeta(): array {
        $coll = static::getAllFromTable(WpPostmetum::TABLE);
        $byPostId = [];
        /** @var WpPostmetum $item */
        foreach($coll as $item){
            $byPostId[$item->post_id][$item->meta_key] = $item;
        }
        return $byPostId;
    }
    /**
     * @return \Illuminate\Support\Collection|WpPost[]
     */
    public static function getAttachments(){
        return static::getPosts('attachment');
    }
	public static function getConnectionName(): string{return static::CONNECTION_NAME;}
protected static function getDBDriverName():string{return 'mysql';}
	public static function getDefaultDBName(): string{
		return Writable::getDbName();
	}
}
