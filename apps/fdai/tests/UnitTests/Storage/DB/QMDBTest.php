<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace Tests\UnitTests\Storage\DB;
use App\Storage\DB\Writable;
use Tests\UnitTestCase;

/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Storage\DB\QMDB
 */
class QMDBTest extends UnitTestCase
{
    public function testGetForeignKeysReferencingGivenTableColumn()
    {
		$this->skipTest("Doesn't work yet with SQLite");
        $foreignKeys = Writable::getForeignKeysReferencingGivenTableColumn('wp_users', 'ID');
        $this->assertNotEmpty($foreignKeys);
        $referencingTables = array_map(function (\Doctrine\DBAL\Schema\ForeignKeyConstraint $item) {
            return $item->getLocalTableName();
        }, $foreignKeys);
        sort($referencingTables);
        $referencingColumns = array_map(function (\Doctrine\DBAL\Schema\ForeignKeyConstraint $item) {
            return $item->getLocalColumns()[0];
        }, $foreignKeys);
        sort($referencingColumns);
        $this->assertArrayEquals(array (
            0 => 'child_user_id',
            1 => 'link_owner',
            2 => 'parent_user_id',
            3 => 'patient_user_id',
            4 => 'physician_user_id',
            5 => 'post_author',
            6 => 'referrer_user_id',
            7 => 'sharer_user_id',
            8 => 'subscriber_user_id',
            9 => 'trustee_user_id',
            10 => 'user_id',
            11 => 'user_id',
            12 => 'user_id',
            13 => 'user_id',
            14 => 'user_id',
            15 => 'user_id',
            16 => 'user_id',
            17 => 'user_id',
            18 => 'user_id',
            19 => 'user_id',
            20 => 'user_id',
            21 => 'user_id',
            22 => 'user_id',
            23 => 'user_id',
            24 => 'user_id',
            25 => 'user_id',
            26 => 'user_id',
            27 => 'user_id',
            28 => 'user_id',
            29 => 'user_id',
            30 => 'user_id',
            31 => 'user_id',
            32 => 'user_id',
            33 => 'user_id',
            34 => 'user_id',
            35 => 'user_id',
            36 => 'user_id',
            37 => 'user_id',
            38 => 'user_id',
            39 => 'user_id',
            40 => 'user_id',
            41 => 'user_id',
            42 => 'user_id',
            43 => 'user_id',
            44 => 'user_id',
            45 => 'user_id',
            46 => 'user_id',
            47 => 'user_id',
            48 => 'user_id',
        ), $referencingColumns);
        $this->assertArrayEquals(array (
            0 => 'applications',
            1 => 'button_clicks',
            2 => 'buttons',
            3 => 'cards',
            4 => 'child_parents',
            5 => 'child_parents',
            6 => 'cohort_studies',
            7 => 'collaborators',
            8 => 'connections',
            9 => 'connector_imports',
            10 => 'connector_requests',
            11 => 'correlation_causality_votes',
            12 => 'correlation_usefulness_votes',
            13 => 'user_variable_relationships',
            14 => 'credentials',
            15 => 'device_tokens',
            16 => 'github_repositories',
            17 => 'global_studies',
            18 => 'lightsail_instances',
            19 => 'measurement_exports',
            20 => 'measurement_imports',
            21 => 'measurements',
            22 => 'oa_access_tokens',
            23 => 'oa_authorization_codes',
            24 => 'oa_clients',
            25 => 'oa_refresh_tokens',
            26 => 'patient_physicians',
            27 => 'patient_physicians',
            28 => 'permission_user',
            29 => 'phrases',
            30 => 'purchases',
            31 => 'role_user',
            32 => 'sent_emails',
            33 => 'sharer_trustees',
            34 => 'sharer_trustees',
            35 => 'studies',
            36 => 'tracking_reminder_notifications',
            37 => 'tracking_reminders',
            38 => 'user_clients',
            39 => 'user_studies',
            40 => 'user_tags',
            41 => 'user_variable_clients',
            42 => 'user_variables',
            43 => 'variable_user_sources',
            44 => 'votes',
            45 => 'wp_links',
            46 => 'wp_posts',
            47 => 'wp_usermeta',
            48 => 'wp_users',
        ), $referencingTables);
    }
}
