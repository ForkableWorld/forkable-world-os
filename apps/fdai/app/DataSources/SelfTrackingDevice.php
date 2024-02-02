<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DataSources;
use App\DataSources\Connectors\FitbitConnector;
use App\Products\AmazonHelper;
use App\Storage\Memory;
use App\VariableCategories\PhysicalActivityVariableCategory;

class SelfTrackingDevice extends QMDataSource {
    /**
     * SelfTrackingDevice constructor.
     * @param $userId
     * @param array $requestParams
     */
    public function __construct($userId, $requestParams = []){
    }
    /**
     * @return QMConnector[]
     */
    private static function getDevicesArray(){
        return [
            14 => [
                'id'                          => 15,
                'name'                        => 'fitbit',
                'connectorClientId'           => FitbitConnector::instance()->getConnectorClientId(),
                'display_name'                => 'Fitbit Aria',
                'image'                       => 'https://i.imgur.com/TvcY72v.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B0077L8YOO/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B0077L8YOO&linkCode=as2&linkId=SA4S5MBHAWYOVRCA',
                'short_description'           => 'Track your weight body fat percentage and body mass index (BMI).',
                'long_description'            => 'Upload your stats automatically via wi-fi to fitbit.com to see graphs of your progress. Easy to set-up the aria will automatically recognize up to eight users. With aria each account is password protected so you can control how much and what data to share',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            15 => [
                'id'                          => 16,
                'name'                        => 'fitbit',
                'connectorClientId'           => FitbitConnector::instance()->getConnectorClientId(),
                'display_name'                => 'Fitbit Charge Large',
                'image'                       => 'https://i.imgur.com/QuaWm4s.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00N2BVOUE/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00N2BVOUE&linkCode=as2&linkId=RSHDNYKBKOWN7SQ3',
                'short_description'           => 'Track all-day stats like steps taken, distance traveled, calories burned, stairs climbed, and active minutes',
                'long_description'            => 'See daily stats, time of day, and exercise mode with a bright OLED display. Monitor your sleep automatically and wake with a silent alarm. Get call notifications right on your wrist. Access real-time run stats like time, distance, and pace to stay on track.',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            16 => [
                'id'                          => 18,
                'name'                        => 'fitbit',
                'connectorClientId'           => FitbitConnector::instance()->getConnectorClientId(),
                'display_name'                => 'Fitbit Charge HR',
                'image'                       => 'https://i.imgur.com/fETDac6.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00N2BW2PK/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00N2BW2PK&linkCode=as2&linkId=GTRHYTT2ZNGMWJD2',
                'short_description'           => 'Track workouts, heart rate, distance, calories burned, floors climbed, active minutes and steps',
                'long_description'            => 'Get continuous, automatic, wrist-based heart rate and simplified heart rate zones. Monitor your sleep automatically and wake with a silent alarm. See call notifications, daily stats and time of day on the OLED display.',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            17 => [
                'id'                          => 19,
                'name'                        => 'fitbit',
                'connectorClientId'           => FitbitConnector::instance()->getConnectorClientId(),
                'display_name'                => 'FitBit Flex',
                'image'                       => 'https://i.imgur.com/AGaWMOC.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00BSQ6734/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00BSQ6734&linkCode=as2&linkId=LBFK2VDUPBDGAX2H',
                'short_description'           => 'Tracks steps, distance, calories burned and active minutes.',
                'long_description'            => 'Monitor how long and well you sleep. Wakes you (and not your partner) with a silent wake alarm. LED lights show how your day is stacking up against your goal',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            18 => [
                'id'                          => 20,
                'name'                        => 'fitbit',
                'connectorClientId'           => FitbitConnector::instance()->getConnectorClientId(),
                'display_name'                => 'Fitbit One',
                'image'                       => 'https://i.imgur.com/BjWss4u.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B0095PZHPE/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B0095PZHPE&linkCode=as2&linkId=WCVLPAGFK5FPL37B',
                'short_description'           => 'Tracks steps, distance, calories burned and stairs climbed.',
                'long_description'            => 'Monitor how long and how well you sleep. Wakes you (and not your partner) with a silent alarm. Syncs automatically to your computer or select smartphones and tablets via Bluetooth 4.0. Set goals, view progress and earn badges.',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            19 => [
                'id'                          => 21,
                'name'                        => 'fitbit',
                'connectorClientId'           => FitbitConnector::instance()->getConnectorClientId(),
                'display_name'                => 'Fitbit Zip',
                'image'                       => 'https://i.imgur.com/VnGE27L.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B0095PZHZE/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B0095PZHZE&linkCode=as2&linkId=HAQ7UVMEF2FXT3VK',
                'short_description'           => 'Tracks steps, distance and calories burned.',
                'long_description'            => 'Syncs automatically to your computer or select bluetooth 4.0 smartphones or tablets. Set goals, view progress and earn badges. Share and compete with friends throughout the day. Free iphone and android application.',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            20 => [
                'id'                          => 22,
                'name'                        => 'up',
                'display_name'                => 'JAWBONE Up Move',
                'image'                       => 'https://i.imgur.com/B2ciGCh.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00N3BS1I6/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00N3BS1I6&linkCode=as2&linkId=FR7ABVTUNATBA7NX',
                'short_description'           => 'Tracks steps, exercise, overall calories burned, hours slept and quality of sleep, food, drink, calories, nutrients and use the UP App Food Score to quickly know if you are eating right.',
                'long_description'            => 'Guides you to get fit, lose weight and have fun doing it. For best results, wear your UP MOVE on your wrist at night while you sleep. Compatible with iOS and Android',
                'enabled'                     => 1,
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            21 => [
                'id'                          => 24,
                'name'                        => 'withings',
                'display_name'                => 'Withings Activite',
                'image'                       => 'https://i.imgur.com/63sUPmf.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00NLAGYAQ/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00NLAGYAQ&linkCode=as2&linkId=226P5MIDVKGQYKPY',
                'short_description'           => 'Tracks steps, distance and calories burned.',
                'long_description'            => '"Recognizes you are running and swimming automatically; Activity is water resistant up to 5 ATM (165 feet or 50 meters).',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            22 => [
                'id'                          => 25,
                'name'                        => 'withings',
                'display_name'                => 'Withings Activite Pop',
                'image'                       => 'https://i.imgur.com/0YN68RX.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00S5I9H4O/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00S5I9H4O&linkCode=as2&linkId=DRJMSU3SB5VBEQFL',
                'short_description'           => 'Tracks walking and running steps taken.',
                'long_description'            => 'Selectable daily goals, smart Touch detection, smart alarm and Silicone Band.',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            23 => [
                'id'                          => 26,
                'name'                        => 'withings',
                'display_name'                => 'Withings Aura',
                'image'                       => 'https://i.imgur.com/qbqfgJw.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00LC2VWJI/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00LC2VWJI&linkCode=as2&linkId=HFMHPYS3ME6TIB3F',
                'short_description'           => 'Track your sleep on your smartphone (iOS).',
                'long_description'            => 'Track and improve transitions into and out of sleep with a combination of sound and light effects. Smart Wake Up Light: wake up feeling rested at the best time in your sleep cycle. Sunset lighting relaxes you as you drift to sleep. Enjoy naps and relaxing sessions with specialized programs.',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            24 => [
                'id'                          => 28,
                'name'                        => 'withings',
                'display_name'                => 'Withings Pulse',
                'image'                       => 'https://i.imgur.com/prB3Y60.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00CW7KK9K/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00CW7KK9K&linkCode=as2&linkId=LI7NSG6YR4AUSE4Z',
                'short_description'           => 'Tracks steps, elevation, distance and calories burned.',
                'long_description'            => 'Instant Heart Rate measurement. Sleep quality monitoring. iPhone (3GS or later), iPad (except the original iPad - App not iPad optimized) or iPod touch (4th generation or later), with iOS 6.0 or later and Internet access (mobile data or Wi-Fi). Bluetooth-enabled Android smartphone or tablet',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            25 => [
                'id'                          => 29,
                'name'                        => 'withings',
                'display_name'                => 'Withings Pulse O2',
                'image'                       => 'https://i.imgur.com/BlIu9VP.jpg',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00J8LVJ98/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00J8LVJ98&linkCode=as2&linkId=OMUZMFUILW6ZIFM2',
                'short_description'           => 'Tracks steps, elevation, distance, running and calories burned.',
                'long_description'            => 'Wear it your way: clip and wristband included. Vital signs reading: instant heart rate and blood oxygen level. Sleep monitoring: sleep cycle analysis, wake-ups, total duration. Real-time coaching: in the free Health Mate app (iOS/Android).',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            26 => [
                'id'                          => 31,
                'name'                        => 'withings',
                'display_name'                => 'Withings Smart Body Analyzer',
                'image'                       => 'https://i.imgur.com/BJWQoSR.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00K0OPIUI/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00K0OPIUI&linkCode=as2&linkId=WBGS7LWVUHDSSTYJ',
                'short_description'           => 'Tracks air quality, heart rate, fat mass and weight.',
                'long_description'            => 'Air quality screening, reads heart rate, reads fat mass, automatic user recognition, tells weight.',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            27 => [
                'id'                          => 32,
                'name'                        => 'withings',
                'display_name'                => 'Withings Smart Kid Scale',
                'image'                       => 'https://i.imgur.com/uTBgJMG.jpg?1',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00ATZU9HA/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00ATZU9HA&linkCode=as2&linkId=DYZUMD4W3P7CGB3G',
                'short_description'           => 'Tracks baby\'s weight.',
                'long_description'            => 'Accurately measures your baby\'s weight, even when he is restless on the scale (0.4 ounce increments). Converts from a baby to a toddler scale, track your child\'s growth up to 55 pounds by removing the cradle. Automatically syncs readings with your personal account in Wi-Fi or bluetooth.',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            28 => [
                'id'                          => 33,
                'name'                        => 'withings',
                'display_name'                => 'Withings WiFi Body Scale',
                'image'                       => 'https://i.imgur.com/24DCkds.jpg',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B002JE2PSA/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B002JE2PSA&linkCode=as2&linkId=YLJEAXH7HJEKREYI',
                'short_description'           => 'Tracks weight, BMI and fat mass.',
                'long_description'            => 'Accurate Weight and BMI measurement with Position Control. Body Fat monitoring with FDA-cleared bioelectrical impedance analysis. Wirelessly uploads using your Wi-Fi network. . Multi-user support with automatic recognition. Easy weight and fat mass monitoring on a private website secured by a password.',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            29 => [
                'id'                          => 34,
                'name'                        => 'withings',
                'display_name'                => 'Withings Wireless BP Monitor',
                'image'                       => 'https://i.imgur.com/E1adJWc.jpg',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00H43WOAK/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00H43WOAK&linkCode=as2&linkId=VS4XIOJMRMEUTEW5',
                'short_description'           => 'Tracks blood pressure.',
                'long_description'            => 'Automatic wireless sync with withings health mate app. Detailed results and recommended values displayed in the app. Dual connectivity (Bluetooth + wired). Compatible iOS and android.High-accuracy blood pressure monitoring',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:05',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            30 => [
                'id'                          => 35,
                'name'                        => 'withings',
                'display_name'                => 'Withings Wireless Scale WS-30',
                'image'                       => 'https://i.imgur.com/xOLfxPg.jpg',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00AXYL4M6/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00AXYL4M6&linkCode=as2&linkId=Q2F3N2WAVOWI23M4',
                'short_description'           => 'Tracks weight and BMI measurement with Position Control.',
                'long_description'            => 'Wirelessly uploads in Wi-Fi and Bluetooth. Health Mate app to visualize weight trends. Multi-user support with automatic recognition. Easy set up from iOS app, one tap Wi-Fi configuration sharing.',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:06',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
            31 => [
                'id'                          => 36,
                'name'                        => 'withings',
                'display_name'                => 'Withings Wireless Scale WS-50',
                'image'                       => 'https://i.imgur.com/PEE99gr.jpg',
                'get_it_url'                  => 'http://www.amazon.com/gp/product/B00BKRQ4E8/ref=as_li_qf_sp_asin_il_tl?ie=UTF8&camp=1789&creative=9325&creativeASIN=B00BKRQ4E8&linkCode=as2&linkId=ROPQXIN7SADVM57F',
                'short_description'           => 'Tracks heart rate.',
                'long_description'            => 'Ultra precise weight and body fat measurement with position control. Automatic upload of your measurements in WiFi and Bluetooth.  The scale interface is designed to help users center on the product to obtain accurate readings. Heart rate measurement by stepping on the scale and continuous indoor air quality tracking',
                'enabled'                     => 1,
                'updated_at'                  => '2016-03-03 02:08:06',
                'affiliate'                   => true,
                'defaultVariableCategoryName' => PhysicalActivityVariableCategory::NAME
            ],
        ];
    }
    /**
     * @return QMConnector[]
     */
    public static function getSelfTrackingDevices(){
        if($clients = Memory::get(Memory::SELF_TRACKING_DEVICES,Memory::MISCELLANEOUS)){
            return $clients;
        }
        $selfTrackingDevices = self::getDevicesArray();
        $selfTrackingDevices = QMDataSource::processDataSources($selfTrackingDevices);
        Memory::set(Memory::SELF_TRACKING_DEVICES, $selfTrackingDevices);
        return $selfTrackingDevices;
    }
}
