<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables;
use App\Models\CommonTag;
/**
 * @mixin CommonTag
 */
class QMCommonTag extends QMTag {
    public const TABLE = 'common_tags';
    /**
     * CommonTag constructor.
     * @param $id
     * @param $taggedVariableId
     * @param $tagVariableId
     * @param $conversionFactor
     * @param $clientId
     */
    public function __construct(int $id = null, int $taggedVariableId = null, int $tagVariableId = null,
                                float $conversionFactor = null, string $clientId = null){
        parent::__construct($id, $taggedVariableId, $tagVariableId, $conversionFactor, $clientId);
    }
}
