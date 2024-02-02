<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

/** Created by PhpStorm.
 * User: m
 * Date: 9/3/2017
 * Time: 8:08 AM
 */
namespace App\Variables;
class QMUserVariableV3 extends QMUserVariable {
    public $defaultUnitAbbreviatedName;
    public $defaultUnitId;
    public $defaultUnitName;
    public $numberOfUserVariableRelationshipsAsCause;
    public $numberOfUserVariableRelationshipsAsEffect;
    public $userUnitAbbreviatedName;
    public $userUnitId;
    public $userUnitName;
    /**
     * @param QMUserVariable[] $variables
     * @return array
     */
    public static function convert(array $variables){
        foreach($variables as $userVariableV4){
            foreach($userVariableV4->getUserUnit() as $key => $value){
                $key = "userUnit".ucfirst($key);
                $userVariableV4->$key = $value;
            }
            foreach($userVariableV4->getUserUnit() as $key => $value){
                $key = "unit".ucfirst($key);
                $userVariableV4->$key = $value;
            }
            foreach($userVariableV4->getCommonUnit() as $key => $value){
                $key = "defaultUnit".ucfirst($key);
                $userVariableV4->$key = $value;
            }
            foreach($userVariableV4->getQMVariableCategory() as $key => $value){
                $key = "variableCategory".ucfirst($key);
                $userVariableV4->$key = $value;
            }
            $userVariableV4->numberOfUserVariableRelationshipsAsCause = $userVariableV4->numberOfCorrelationsAsCause;
            $userVariableV4->numberOfUserVariableRelationshipsAsEffect = $userVariableV4->numberOfCorrelationsAsEffect;
        }
        return $variables;
    }
}
