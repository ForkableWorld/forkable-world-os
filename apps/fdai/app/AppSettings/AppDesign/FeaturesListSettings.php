<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\AppSettings\AppDesign;
use App\UI\ImageHelper;
use App\AppSettings\AppDesign;
use App\AppSettings\AppSettings;
use App\VariableCategories\SleepVariableCategory;
use App\Variables\QMVariableCategory;
class FeaturesListSettings extends AppDesignSettings {
    /**
     * Onboarding constructor.
     * @param AppSettings|object $appSettings
     */
    public function __construct($appSettings = null){
        if(!isset($appSettings->appDesign->featuresList)){
            $appSettings->appDesign->featuresList = $this;
        }
        $this->appSettings = $appSettings;
        //$this->active = $appSettings->appDesign->featuresList->active ?? self::getDefaultFeaturesList();
        //$this->custom = $appSettings->appDesign->featuresList->custom ?? self::getDefaultFeaturesList();
        // Need to overwrite old FeaturesList image links
        $this->active =self::getDefaultFeaturesList();
        $this->custom = self::getDefaultFeaturesList();
        $this->type = $appSettings->appDesign->featuresList->type ?? 'general';
        $this->active = AppDesign::removeNullItemsFromArray($this->active);
        $this->custom = AppDesign::removeNullItemsFromArray($this->custom);
    }
    /**
     * @return Feature[]
     */
    public static function getDefaultFeaturesList(): array{
        $list = [
            [
                'title'    => 'Emotion Tracking',
                'subtitle' => 'Turn data into happiness!',
                'moreInfo' => QMVariableCategory::find('Emotions')->moreInfo,
                'image'    => QMVariableCategory::find('Emotions')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Track Symptoms',
                'subtitle' => 'in just seconds a day',
                'moreInfo' => QMVariableCategory::find('Symptoms')->moreInfo,
                'image'    => QMVariableCategory::find('Symptoms')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Track Diet',
                'subtitle' => 'Identify dietary triggers',
                'moreInfo' => QMVariableCategory::find('Foods')->moreInfo,
                'image'    => QMVariableCategory::find('Foods')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Treatment Tracking',
                'subtitle' => 'with reminders',
                'moreInfo' => QMVariableCategory::find('Treatments')->moreInfo,
                'image'    => QMVariableCategory::find('Treatments')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Weather Tracking',
                'subtitle' => 'Automatically log weather',
                'moreInfo' => QMVariableCategory::find('Environment')->moreInfo,
                'image'    => QMVariableCategory::find('Environment')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Sleep Quality',
                'subtitle' => 'Create a Sleep Quality reminder to record your sleep quality every day',
                'moreInfo' => SleepVariableCategory::MORE_INFO,
                'image'    => SleepVariableCategory::IMAGE_URL,
                'premium'  => false
            ],
            [
                'title'    => 'Import from Apps',
                'subtitle' => 'Facebook, Google Calendar, Runkeeper, Github, Sleep as Android, MoodiModo, and even '.'the weather!',
                'moreInfo' => "Automatically import your data from Google Calendar, Facebook, Runkeeper, "."QuantiModo, Sleep as Android, MoodiModo, Github, and even the weather!",
                'image'    => ImageHelper::BASE_URL.'features/smartphone.svg',
                'premium'  => false
            ],
            [
                'title'    => 'Sync Across Devices',
                'subtitle' => 'Web, Chrome, Android, and iOS',
                'moreInfo' => "Any of your supported apps will automatically sync with any other app "."on the web, Chrome, Android, and iOS.",
                'image'    => ImageHelper::BASE_URL.'features/devices.svg',
                'premium'  => false
            ],
            //            [
            //                'title' =>  'Unlimited History',
            //                'subtitle' =>  'Lite gets 3 months',
            //                'moreInfo' =>  "Premium accounts can see unlimited historical data (Free accounts can see only "  .
            //                    "the most recent three months). This is great for seeing long-term trends in your " .
            //                    "productivity or getting totals for the entire year.",
            //                'image' =>  ImageHelper::IMAGE_BASE_URL.'features/calendar.svg',
            //                'premium' => false
            //            ],
            [
                'title'    => 'Location Tracking',
                'subtitle' => 'Automatically log places',
                'moreInfo' => QMVariableCategory::find('Location')->moreInfo,
                'image'    => QMVariableCategory::find('Location')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Purchase Tracking',
                'subtitle' => 'Automatically log purchases',
                'moreInfo' => QMVariableCategory::find('Payments')->moreInfo,
                'image'    => QMVariableCategory::find('Payments')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Productivity Tracking',
                'subtitle' => 'Passively track app usage',
                'moreInfo' => "You can do this by installing and connecting Rescuetime on the Import Data page.  Rescuetime is a program"." that runs on your computer & passively tracks of productivity and app usage.",
                'image'    => ImageHelper::BASE_URL.'features/rescuetime.png',
                'premium'  => false
            ],
            [
                'title'    => 'Automated Physique Tracking',
                'subtitle' => 'Monitor weight and body fat with the Withings body analyzer',
                'moreInfo' => QMVariableCategory::find('Physique')->moreInfo,
                'image'    => QMVariableCategory::find('Physique')->imageUrl,
                'premium'  => false
            ],
            [
                'title'    => 'Find Out What Works',
                'subtitle' => 'Discover hidden factors improving or worsening symptoms and well-being',
                'moreInfo' => "See a list of the strongest predictors for any outcome.  See the values for each "."predictor that typically precede optimal outcomes.  Dive deeper by checking "."out the full study on any predictor and outcome combination.",
                'image'    => ImageHelper::BASE_URL.'data/graph.png',
                'premium'  => false
            ],
            [
                'title'    => 'Import from Devices',
                'subtitle' => 'Fitbit, Jawbone Up, Withings...',
                'moreInfo' => "Automatically import your data from Fitbit, Withings, Jawbone.",
                'image'    => ImageHelper::getImageUrl('features/smartwatch.svg'),
                'premium'  => true
            ],
            [
                'title'    => 'Automated Vital Signs',
                'subtitle' => 'Keep your heart healthy',
                'moreInfo' => "I can get your heart rate data from the Fitbit Charge HR, Fitbit Surge.  "."Resting heart rate is a good measure of general fitness, and heart rate during "."workouts show intensity.  I can also talk to Withing's bluetooth blood pressure monitor. ",
                'image'    => ImageHelper::BASE_URL.'features/heart-like.png',
                'premium'  => true
            ],
            [
                'title'    => 'Automated Fitness Tracking',
                'subtitle' => 'Steps and physical activity',
                'moreInfo' => QMVariableCategory::find('Physical Activity')->moreInfo,
                'image'    => QMVariableCategory::find('Physical Activity')->imageUrl,
                'premium'  => true
            ],
            [
                'title'    => 'Automatic Sleep Tracking',
                'subtitle' => 'Automatically track sleep duration and quality by importing Fitbit data.',
                'moreInfo' => QMVariableCategory::find('Sleep')->moreInfo,
                'image'    => QMVariableCategory::find('Sleep')->imageUrl,
                'premium'  => true
            ],
            [
                'title'    => 'Ad Free!',
                'subtitle' => 'Help support continued improvements and reduce suffering through accelerated scientific discovery!',
                'moreInfo' => "No ads!  What more do you want?",
                'image'    => ImageHelper::BASE_URL.'features/smartphone.svg',
                'premium'  => true
            ]
        ];
        $featureObjects = [];
        foreach($list as $featureArrayItem){
            $featureObjects[] = new Feature($featureArrayItem);
        }
        return $featureObjects;
    }
}
