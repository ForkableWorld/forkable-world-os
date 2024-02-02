<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\ModelValidationException;
use App\Logging\QMLog;
use App\Models\OAAccessToken;
use App\Models\User;
use App\Properties\Base\BaseNumberOfPatientsProperty;
use App\Traits\HasPatients;
use App\Traits\PropertyTraits\UserProperty;
class UserNumberOfPatientsProperty extends BaseNumberOfPatientsProperty
{
    use UserProperty;
    public $name = User::FIELD_NUMBER_OF_PATIENTS;
    /**
     * @param HasPatients|User $model
     * @return int
     */
    public static function calculate($model): int{
	    $val = $model->getPatientAccessTokens()->count();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    public static function increment(int $userId){
        User::whereID($userId)
            ->increment(User::FIELD_NUMBER_OF_PATIENTS);
    }
    public function validate(): void {
        $val = $this->getDBValue();
        $previous = $this->getRawOriginal();
        if($val < $previous){
            $calculated = static::calculate($this->getUser());
            if($val !== $calculated){
                $this->throwException("$val does not equal calculated value $calculated");
            }
        }
        parent::validate();
    }
    public static function updateAll(){
        $ids = OAAccessToken::getPhysicianUserIds();
        QMLog::info(__METHOD__." for ".count($ids)." physician users...");
        foreach($ids as $id){
            $user = User::findInMemoryOrDB($id);
            $val = self::calculate($user);
            $user->logInfo("$val patients");
            try {
                $user->save();
            } catch (ModelValidationException $e) {
                le($e);
            }
        }
    }
}
