<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection SpellCheckingInspection */
namespace App\Properties\Study;
use App\Models\Study;
use App\Properties\Base\BaseUserStudyTextProperty;
use App\Traits\PropertyTraits\IsString;
use App\Traits\PropertyTraits\StudyProperty;
use App\Types\QMStr;
use App\Utils\SecretHelper;
use Throwable;

class StudyUserStudyTextProperty extends BaseUserStudyTextProperty
{
    use StudyProperty;
    use IsString;
    protected $shouldNotContain = self::BLACKLISTED_STRINGS;
    public const BLACKLISTED_STRINGS = [
        "Average  Predictor  Treatment  Value", // No extra spaces!
        'pimunsi6t5ysd81k',
        'Principal Investigator System',
        'Your app key is missing',
        'After treatment, a Unknown',
    ];
    public $table = Study::TABLE;
    public $parentClass = Study::class;
    /**
     * @param string $html
     */
    public static function validateStudyHtml(string $html){
        if(stripos($html, "Internal Error") !== false){
            le("Internal Error");
        }
        try {
            QMStr::assertStringDoesNotContain($html,
                StudyUserStudyTextProperty::BLACKLISTED_STRINGS,
                __FUNCTION__);
            SecretHelper::exceptionIfContainsSecretValue($html, __METHOD__);
        } catch (Throwable $e) {
            le($e);
        }
    }
}
