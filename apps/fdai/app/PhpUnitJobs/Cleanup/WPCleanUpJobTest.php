<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Cleanup;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Models\WpPost;
use App\Models\WpTerm;
use App\Models\WpTermTaxonomy;
use App\PhpUnitJobs\JobTestCase;
use App\Storage\DB\Migrations;
use App\Storage\DB\ReadonlyDB;
use App\Studies\QMStudy;
use App\Types\QMStr;
use App\UI\HtmlHelper;
use Illuminate\Support\Str;
class WPCleanUpJobTest extends JobTestCase {
    public static function testWPCleanup(){
        WpTermTaxonomy::updateCategoryCounts();
        self::deleteStupidCategories();
        self::listPostSizes();
        WpPost::forceDeleteWherePostNameLike('https-');
    }
    public static function deleteStupidCategories(){
        foreach(WpPost::STUPID_CATEGORY_NAMES as $stupid => $new){
            $stupidTerm = WpTerm::whereName($stupid)->first();
            $goodTerm = WpTerm::firstOrCreate([WpTerm::FIELD_NAME => $new, WpTerm::FIELD_SLUG => Str::slug($new)]);
            /** @var WpTermTaxonomy $taxonomy */
            $taxonomy = $stupidTerm->wp_term_taxonomies->first();
            $relationships = $taxonomy->wp_term_relationships;
            foreach($relationships as $relationship){
	            $post = $relationship->wp_post;
                $post->replaceCategory($goodTerm, $stupid);
            }
        }
    }
    public static function listPostSizes(){
        $POST_SIZE_SQL = '
    select SUBSTRING(post_name, 1, 32) as post_name,
        round(length(post_content)/1000) as content_KB,
        round(length(post_content_filtered)/1000) as content_filtered_KB,
        SUBSTRING(post_title, 1, 32) as post_title,
        post_type,
        SUBSTRING(guid, 1, 64) as guid
    from wp_posts ';

        ReadonlyDB::logSelectToTable($POST_SIZE_SQL."
    order by length(post_content_filtered) desc
    limit 100", "largest post_content_filtered");

        ReadonlyDB::logSelectToTable($POST_SIZE_SQL."
    order by length(post_content) desc
    limit 100", "largest post content");
    }
    public function testGenerateCommentMigrations(){
        $str = self::getDescriptions();
        //$str = StringHelper::removeLineBreaks($str);
        $tables = explode("<h3 ", $str);
        foreach($tables as $tableStr){
            if(empty($tableStr)){continue;}
            if(strlen($tableStr) < 10){continue;}
            $tableName = QMStr::between($tableStr, "\">", "</h3>");
            $tableDescription = QMStr::between($tableStr, "<p>", "</p>");
            Migrations::makeMigration("alter table $tableName
                comment '$tableDescription';", "alter table $tableName
                comment '$tableDescription';");
            $ul = QMStr::between($tableStr, "<ul>", "</ul>");
            $columns = HtmlHelper::htmlUnorderedListToArray("<ul>$ul</ul>");
            foreach($columns as $columnStr){
                $columnName = QMStr::between($columnStr, "<strong>", "</strong>");
                $comment = QMStr::after("</strong> – ", $columnStr);
                $comment = ucfirst($comment);
                $comment = QMStr::before(" (Ref", $comment, $comment);
                Migrations::commentMigration($tableName, $columnName, $comment);
            }
        }
    }
    /**
     * @return int
     */
    public static function deleteAllWpPosts(){
        $array = [
            GlobalVariableRelationship::FIELD_PUBLISHED_AT => null,
            GlobalVariableRelationship::FIELD_WP_POST_ID   => null
        ];
        QMGlobalVariableRelationship::writable()->update($array);
        QMUserVariableRelationship::writable()->update($array);
        $result = QMStudy::writable()->update($array);
        //QMWordPressApi::deleteAllPosts();
        WpPost::deleteAllPosts();
        return $result;
    }
    /**
     * @return string
     */
    private static function getDescriptions():string{
        return "
<h3 id=\"wp_posts\">wp_posts</h3>
<p>The posts table is arguably the most important table in the WordPress database. Its name sometimes throws people who believe it purely contains their blog posts. However, albeit badly named, it is an extremely powerful table that stores various types of content including posts, pages, menu items, media attachments and any custom post types that a site uses.</p>
<p>The table’s flexible content nature is provided by the ‘post_type’ column which denotes if the row is a post, page, attachment, nav_menu_item or another type. But this flexibility also makes it hard to discuss and describe. Essentially the table contains rows of content objects with different types, but for ease of reading, I will refer to the rows as “posts” throughout this article.</p>
<ul>
<li><strong>ID</strong> – unique number assigned to each post.</li>
<li><strong>post_author</strong> – the user ID who created it. (Reference to the <a href=\"#wp_users\">wp_users</a> table.)</li>
<li><strong>post_date</strong> – time and date of creation.</li>
<li><strong>post_date_gmt</strong> – GMT time and date of creation. The GMT time and date is stored so there is no dependency on a site’s timezone in the future.</li>
<li><strong>post_content</strong> – holds all the content for the post, including HTML, shortcodes and other content.</li>
<li><strong>post_title</strong> – title of the post.</li>
<li><strong>post_excerpt</strong> – custom intro or short version of the content.</li>
<li><strong>post_status</strong> – status of the post, e.g. ‘draft’, ‘pending’, ‘private’, ‘publish’. Also a great WordPress <a href=\"https://poststatus.com/\" target=\"_blank\">news site</a>.</li>
<li><strong>comment_status</strong> – if comments are allowed.</li>
<li><strong>ping_status</strong> – if the post allows <a href=\"http://codex.wordpress.org/Introduction_to_Blogging#Pingbacks\" target=\"_blank\">ping and trackbacks</a>.</li>
<li><strong>post_password</strong> – optional password used to view the post.</li>
<li><strong>post_name</strong> – URL friendly slug of the post title.</li>
<li><strong>to_ping</strong> – a list of URLs WordPress should send pingbacks to when updated.</li>
<li><strong>pinged</strong> – a list of URLs WordPress has sent pingbacks to when updated.</li>
<li><strong>post_modified</strong> – time and date of last modification.</li>
<li><strong>post_modified_gmt</strong> – GMT time and date of last modification.</li>
<li><strong>post_content_filtered</strong> – used by plugins to cache a version of post_content typically passed through the ‘the_content’ filter. Not used by WordPress core itself.</li>
<li><strong>post_parent</strong> – used to create a relationship between this post and another when this post is a revision, attachment or another type.</li>
<li><strong>guid</strong> – Global Unique Identifier, the permanent URL to the post, not the permalink version.</li>
<li><strong>menu_order</strong> – holds the display number for pages and other non-post types.</li>
<li><strong>post_type</strong> – the content type identifier.</li>
<li><strong>post_mime_type</strong> – only used for attachments, the MIME type of the uploaded file.</li>
<li><strong>comment_count</strong> – total number of comments, pingbacks and trackbacks.</li>
</ul>
<h3 id=\"wp_postmeta\">wp_postmeta</h3>
<p>This table holds any extra information about individual posts. It is a vertical table using key/value pairs to store its data, a technique WordPress employs on a number of tables throughout the database allowing WordPress core, plugins and themes to store unlimited data.</p>
<ul>
<li><strong>meta_id</strong> – unique number assigned to each row of the table.</li>
<li><strong>post_id</strong> – the ID of the post the data relates to. (Reference to the <a href=\"#wp_posts\">wp_posts</a> table.)</li>
<li><strong>meta_key</strong> – an identifying key for the piece of data.</li>
<li><strong>meta_value</strong> – the actual piece of data.</li>
</ul>
<h3 id=\"wp_comments\">wp_comments</h3>
<p>Any post that allows discussion can have comments posted to it. This table stores those comments and some specific data about them. Further information can be stored in <a href=\"#wp_commentmeta\">wp_commentmeta</a>.</p>
<ul>
<li><strong>comment_ID</strong> – unique number assigned to each comment.</li>
<li><strong>comment_post_ID</strong> – ID of the post this comment relates to. (Reference to the <a href=\"#wp_posts\">wp_posts</a> table.)</li>
<li><strong>comment_author</strong> – Name of the comment author.</li>
<li><strong>comment_author_email</strong> – Email of the comment author.</li>
<li><strong>comment_author_url</strong> – URL for the comment author.</li>
<li><strong>comment_author_IP</strong> – IP Address of the comment author.</li>
<li><strong>comment_date</strong> – Time and data the comment was posted.</li>
<li><strong>comment_date_gmt</strong> – GMT time and data the comment was posted.</li>
<li><strong>comment_content</strong> – the actual comment text.</li>
<li><strong>comment_karma</strong> – unused by WordPress core, can be used by plugins to help manage comments.</li>
<li><strong>comment_approved</strong> – if the comment has been approved.</li>
<li><strong>comment_agent</strong> – where the comment was posted from, eg. browser, operating system etc.</li>
<li><strong>comment_type</strong> – type of comment: comment, pingback or trackback.</li>
<li><strong>comment_parent</strong> – refers to another comment when this comment is a reply.</li>
<li><strong>user_id</strong> – ID of the comment author if they are a registered user on the site. (Reference to the <a href=\"#wp_users\">wp_users</a> table.)</li>
</ul>
<h3 id=\"wp_commentmeta\">wp_commentmeta</h3>
<p>This table stores any further information related to a comment.</p>
<ul>
<li><strong>meta_id</strong> – unique number assigned to each row of the table.</li>
<li><strong>comment_id</strong> – the ID of the post the data relates to. (Reference to the <a href=\"#wp_comments\">wp_comments</a> table.)</li>
<li><strong>meta_key</strong> – an identifying key for the piece of data.</li>
<li><strong>meta_value</strong> – the actual piece of data.</li>
</ul>
<h3 id=\"wp_terms\">wp_terms</h3>
<p>Terms are items of a taxonomy used to classify objects. Taxonomy what? WordPress allows items like posts and custom post types to be classified in various ways. For example, when creating a post in WordPress, by default you can add a category and some tags to it. Both ‘Category’ and ‘Tag’ are examples of a <a href=\"http://codex.wordpress.org/Taxonomies\" target=\"_blank\">taxonomy</a>, basically a way to group things together.</p>
<p>To classify this post (how meta of me) I would give it a category of ‘Guide’ and tags of ‘database’ and ‘mysql’. The category and tags are terms that would be contained in this table.</p>
<ul>
<li><strong>term_id</strong> – unique number assigned to each term.</li>
<li><strong>name</strong> – the name of the term.</li>
<li><strong>slug</strong> – the URL friendly slug of the name.</li>
<li><strong>term_group</strong> – ability for themes or plugins to group terms together to use aliases. Not populated by WordPress core itself.</li>
</ul>
<h3 id=\"wp_term_taxonomy\">wp_term_taxonomy</h3>
<p>Following the wp_terms example above, the terms ‘Guide’, ‘database’ and ‘mysql’ that are stored in wp_terms don’t exist yet as a ‘Category’ and as ‘Tags’ unless they are given context. Each term is assigned a taxonomy using this table.</p>
<p>The structure of this table allows you to use the same term across different taxonomies. For example ‘Database’ could be used as a category for posts and as a term of a custom taxonomy for a custom post type (think portfolio_category for portfolio items). The term of Database would exist once in wp_terms, but there would be two rows in wp_term_taxonomy for each taxonomy.</p>
<ul>
<li><strong>term_taxonomy_id</strong> – unique number assigned to each row of the table.</li>
<li><strong>term_id</strong> – the ID of the related term. (Reference to the <a href=\"#wp_terms\">wp_terms</a> table.)</li>
<li><strong>taxonomy</strong> – the slug of the taxonomy. This can be the <a href=\"http://codex.wordpress.org/Taxonomies#Default_Taxonomies\" target=\"_blank\">built in taxonomies</a> or any taxonomy registered using <a href=\"http://codex.wordpress.org/Function_Reference/register_taxonomy\" target=\"_blank\">register_taxonomy()</a>.</li>
<li><strong>description</strong> – description of the term in this taxonomy.</li>
<li><strong>parent</strong> – ID of a parent term. Used for hierarchical taxonomies like Categories.</li>
<li><strong>count</strong> – number of post objects assigned the term for this taxonomy.</li>
</ul>
<h3 id=\"wp_term_relationships\">wp_term_relationships</h3>
<p>So far we have seen how terms and their taxonomies are stored in the database, but have yet to see how WordPress stores the critical data when it comes to using taxonomies. This post exists in wp_posts and when we actually assign the category and tags through the WordPress dashboard this is the <a href=\"http://en.wikipedia.org/wiki/Junction_table\" target=\"_blank\">junction table</a> that records that information. Each row defines a relationship between a post (object) in wp_posts and a term of a certain taxonomy in wp_term_taxonomy.</p>
<ul>
<li><strong>object_id</strong> – the ID of the post object. (Reference to the <a href=\"#wp_posts\">wp_posts</a> table.)</li>
<li><strong>term_taxonomy_id</strong> – the ID of the term / taxonomy pair. (Reference to the <a href=\"#wp_term_taxonomy\">wp_term_taxonomy</a> table.)</li>
<li><strong>term_order</strong> – allow ordering of terms for an object, not fully used.</li>
</ul>
<h3 id=\"wp_users\">wp_users</h3>
<p>WordPress’ user management is one of its strongest features and one that makes it great as an application framework. This table is the driving force behind it.</p>
<ul>
<li><strong>ID</strong> – unique number assigned to each user.</li>
<li><strong>user_login</strong> – unique username for the user.</li>
<li><strong>user_pass</strong> – hash of the user’s password.</li>
<li><strong>user_nicename</strong> – display name for the user.</li>
<li><strong>user_email</strong> – email address of the user.</li>
<li><strong>user_url</strong> – URL of the user, e.g. website address.</li>
<li><strong>user_registered</strong> – time and date the user registered.</li>
<li><strong>user_activation_key</strong> – used for resetting passwords.</li>
<li><strong>user_status</strong> – was used in Multisite pre WordPress 3.0 to indicate a spam user.</li>
<li><strong>display_name</strong> – desired name to be used publicly in the site, can be user_login, user_nicename, first name or last name defined in wp_usermeta.</li>
</ul>
<h3 id=\"wp_usermeta\">wp_usermeta</h3>
<p>This table stores any further information related to the users. You will see other user profile fields for a user in the dashboard that are stored here.</p>
<ul>
<li><strong>umeta_id</strong> – unique number assigned to each row of the table.</li>
<li><strong>user_id</strong> – ID of the related user. (Reference to the <a href=\"#wp_users\">wp_users</a> table.)</li>
<li><strong>meta_key</strong> – an identifying key for the piece of data.</li>
<li><strong>meta_value</strong> – the actual piece of data.</li>
</ul>
<h3 id=\"wp_options\">wp_options</h3>
<p>The options table is the place where all of the site’s configuration is stored, including data about the theme, active plugins, widgets, and temporary cached data. It is typically where other plugins and themes store their settings.</p>
<p>The table is another example of a vertical key/value pair table to allow it to store all sorts of data for a variety of purposes.</p>
<ul>
<li><strong>option_id</strong> – unique number assigned to each row of the table.</li>
<li><strong>option_name</strong> – an identifying key for the piece of data.</li>
<li><strong>option_value</strong> – the actual piece of data. The data is often <a href=\"https://deliciousbrains.com/wp-migrate-db-pro/doc/serialized-data/\">serialized</a> so must be handled carefully.</li>
<li><strong>autoload</strong> – controls if the option is automatically loaded by the function <a href=\"http://codex.wordpress.org/Function_Reference/wp_load_alloptions\" target=\"_blank\">wp_load_alloptions()</a> (puts options into object cache on each page load).</li>
</ul>
<p>Did you know that when performing migrations of databases using <a href=\"https://deliciousbrains.com/wp-migrate-db-pro/\">WP Migrate DB Pro</a> you can tell the plugin to preserve specific options in the target database using the <a href=\"https://github.com/deliciousbrains/wp-migrate-db-pro-tweaks/blob/master/wp-migrate-db-pro-tweaks.php#L34\" target=\"_blank\">‘wpmdb_preserved_options’</a> filter?</p>
<h3 id=\"wp_links\">wp_links</h3>
<p>During the rise of popularity of blogging having a blogroll (links to other sites) on your site was very much in fashion. This table holds all those links for you.</p>
<p>Nowadays blogrolls are used less and less and as of WordPress 3.5 the administration of links was removed from the admin UI. The table remains in the database for backwards compatibility and you can use the old link manager UI using this <a href=\"https://wordpress.org/plugins/link-manager/\" target=\"_blank\">plugin</a>.</p>
<ul>
<li><strong>link_id</strong> – unique number assigned to each row of the table.</li>
<li><strong>link_url</strong> – URL of the link.</li>
<li><strong>link_name</strong> – name of the link.</li>
<li><strong>link_image</strong> – URL of an image related to the link.</li>
<li><strong>link_target</strong> – the target frame for the link. e.g. _blank, _top, _none.</li>
<li><strong>link_description</strong> – description of the link.</li>
<li><strong>link_visible</strong> – control if the link is public or private.</li>
<li><strong>link_owner</strong> – ID of user who created the link. (Reference to the <a href=\"#wp_users\">wp_users</a> table.)</li>
<li><strong>link_rating</strong> – add a rating between 0-10 for the link.</li>
<li><strong>link_updated</strong> – time and date of link update.</li>
<li><strong>link_rel</strong> – relationship of link.</li>
<li><strong>link_notes</strong> – notes about the link.</li>
<li><strong>link_rss</strong> – RSS address for the link.</li>
</ul>
<p>Someone has produced a helpful entity relationship diagram to explain the relationships between all the tables and posted it on the <a href=\"http://codex.wordpress.org/Database_Description#Database_Diagram\" target=\"_blank\">WordPress codex</a>. This was created at version 3.8 but the structure is still current:</p>
<p><img src=\"https://cdn.deliciousbrains.com/content/uploads/2017/12/12112837/wordpress-database-tables-diagram.png\" alt=\"Diagram of the WordPress Database\" width=\"500\" height=\"705\" class=\"alignnone size-full wp-image-34824\" srcset=\"https://cdn.deliciousbrains.com/content/uploads/2017/12/12112837/wordpress-database-tables-diagram.png 500w, https://cdn.deliciousbrains.com/content/uploads/2017/12/12112837/wordpress-database-tables-diagram-273x385.png 273w\" sizes=\"(max-width: 500px) 100vw, 500px\"><br>
<i>Source: <a href=\"http://codex.wordpress.org/Database_Description#Database_Diagram\" target=\"_blank\">WordPress</a></i></p>
<p>WordPress is great at doing all the heavy lifting for you when it comes to reading and writing to the database, so even though we might know where the data is stored and how to get it, we always recommend using the <a href=\"http://codex.wordpress.org/Category:API\" target=\"_blank\">WordPress APIs</a> wherever possible.</p>
<h2>That’s A Wrap</h2>
<p>I hope this tour has been helpful and informative. If you are looking for a more detailed description of the database scheme then check out the table details <a href=\"http://codex.wordpress.org/Database_Description#Table_Details\" target=\"_blank\">here</a>. </p>
<p><i>Did you enjoy this post? For more database tours, check out our follow-up post on the <a href=\"https://deliciousbrains.com/wordpress-multisite-database-tour/\">multisite tables.</a></i></p>
		</div>
        ";
    }
}
