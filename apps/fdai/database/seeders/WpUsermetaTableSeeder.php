<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class WpUsermetaTableSeeder extends AbstractSeeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('wp_usermeta')->delete();
        
        \DB::table('wp_usermeta')->insert(array (
            0 => 
            array (
                'umeta_id' => '7812',
                'user_id' => '1',
                'meta_key' => 'first_name',
                'meta_value' => 'Quan T.',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            1 => 
            array (
                'umeta_id' => '7813',
                'user_id' => '1',
                'meta_key' => 'last_name',
                'meta_value' => 'Modo',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            2 => 
            array (
                'umeta_id' => '7814',
                'user_id' => '1',
                'meta_key' => 'nickname',
                'meta_value' => 'quantimodo',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            3 => 
            array (
                'umeta_id' => '7815',
                'user_id' => '1',
                'meta_key' => 'description',
                'meta_value' => '',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            4 => 
            array (
                'umeta_id' => '7816',
                'user_id' => '1',
                'meta_key' => 'rich_editing',
                'meta_value' => 'true',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            5 => 
            array (
                'umeta_id' => '7817',
                'user_id' => '1',
                'meta_key' => 'comment_shortcuts',
                'meta_value' => 'false',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            6 => 
            array (
                'umeta_id' => '7818',
                'user_id' => '1',
                'meta_key' => 'admin_color',
                'meta_value' => 'fresh',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            7 => 
            array (
                'umeta_id' => '7819',
                'user_id' => '1',
                'meta_key' => 'use_ssl',
                'meta_value' => '0',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            8 => 
            array (
                'umeta_id' => '7820',
                'user_id' => '1',
                'meta_key' => 'show_admin_bar_front',
                'meta_value' => 'true',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            9 => 
            array (
                'umeta_id' => '7821',
                'user_id' => '1',
                'meta_key' => 'wp_user_level',
                'meta_value' => '10',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            10 => 
            array (
                'umeta_id' => '7822',
                'user_id' => '1',
                'meta_key' => 'dismissed_wp_pointers',
                'meta_value' => 'wp330_toolbar,wp330_saving_widgets,wp340_choose_image_from_library,wp340_customize_current_theme_link,wp350_media,wp360_revisions,wp360_locks',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            11 => 
            array (
                'umeta_id' => '7823',
                'user_id' => '2',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            12 => 
            array (
                'umeta_id' => '7824',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            13 => 
            array (
                'umeta_id' => '7825',
                'user_id' => '1',
                'meta_key' => 'wp_user_level',
                'meta_value' => '10',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            14 => 
            array (
                'umeta_id' => '7826',
                'user_id' => '1',
                'meta_key' => 'last_activity',
                'meta_value' => '2014-09-26 09:04:41',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            15 => 
            array (
                'umeta_id' => '7827',
                'user_id' => '1',
                'meta_key' => 'wp_user-settings',
                'meta_value' => 'mfold=o&editor=html&hidetb=1&ed_size=609&libraryContent=browse',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            16 => 
            array (
                'umeta_id' => '7828',
                'user_id' => '1',
                'meta_key' => 'wp_user-settings-time',
                'meta_value' => '1398620572',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            17 => 
            array (
                'umeta_id' => '7829',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            18 => 
            array (
                'umeta_id' => '7830',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            19 => 
            array (
                'umeta_id' => '7831',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            20 => 
            array (
                'umeta_id' => '7832',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            21 => 
            array (
                'umeta_id' => '7833',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            22 => 
            array (
                'umeta_id' => '7834',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            23 => 
            array (
                'umeta_id' => '7835',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            24 => 
            array (
                'umeta_id' => '7836',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            25 => 
            array (
                'umeta_id' => '7837',
                'user_id' => '1',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:1:{s:10:"subscriber";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            26 => 
            array (
                'umeta_id' => '7838',
                'user_id' => '1',
                'meta_key' => 'user_login',
                'meta_value' => 'mike',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            27 => 
            array (
                'umeta_id' => '7839',
                'user_id' => '1',
                'meta_key' => 'user_email',
                'meta_value' => 'm@thinkbynumbers.org',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            28 => 
            array (
                'umeta_id' => '7840',
                'user_id' => '1',
                'meta_key' => 'client_id',
                'meta_value' => 'quantimodo',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            29 => 
            array (
                'umeta_id' => '7841',
                'user_id' => '1',
                'meta_key' => 'session_tokens',
            'meta_value' => 'a:1:{s:64:"9f7efd44d97f565f241639a65b328429ace48c2aa791969ded9522ffec70e15e";a:4:{s:10:"expiration";i:1582136858;s:2:"ip";s:9:"127.0.0.1";s:2:"ua";s:115:"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36";s:5:"login";i:1580927258;}}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            30 => 
            array (
                'umeta_id' => '7842',
                'user_id' => '1',
                'meta_key' => 'last_activity',
                'meta_value' => '2020-02-05 18:27:38',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            31 => 
            array (
                'umeta_id' => '7843',
                'user_id' => '1',
                'meta_key' => 'wp_dashboard_quick_press_last_post_id',
                'meta_value' => '23',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            32 => 
            array (
                'umeta_id' => '7844',
                'user_id' => '1',
                'meta_key' => 'community-events-location',
                'meta_value' => 'a:1:{s:2:"ip";s:9:"127.0.0.0";}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            33 => 
            array (
                'umeta_id' => '7845',
                'user_id' => '230',
                'meta_key' => 'session_tokens',
            'meta_value' => 'a:1:{s:64:"a85bfeb80b9a7f18c4e7827ee4916eda42990e71a4d2c3d1a6661d1bf615d20e";a:4:{s:10:"expiration";i:1582787669;s:2:"ip";s:9:"127.0.0.1";s:2:"ua";s:78:"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0";s:5:"login";i:1581578069;}}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            34 => 
            array (
                'umeta_id' => '7846',
                'user_id' => '230',
                'meta_key' => 'wp_capabilities',
                'meta_value' => 'a:2:{s:13:"administrator";b:1;s:15:"bbp_participant";b:1;}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => 'unknown',
            ),
            35 => 
            array (
                'umeta_id' => '7847',
                'user_id' => '230',
                'meta_key' => 'wp_dashboard_quick_press_last_post_id',
                'meta_value' => '24',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
            36 => 
            array (
                'umeta_id' => '7848',
                'user_id' => '230',
                'meta_key' => 'community-events-location',
                'meta_value' => 'a:1:{s:2:"ip";s:9:"127.0.0.0";}',
                'updated_at' => '2020-01-01 00:00:00',
                'created_at' => '2020-01-01 00:00:00',
                'deleted_at' => NULL,
                'client_id' => NULL,
            ),
        ));
        
        
    }
}
