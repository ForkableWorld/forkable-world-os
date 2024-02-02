<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;
use App\VariableCategories\PaymentsVariableCategory;
use App\Variables\QMVariable;
class InvalidTagCategoriesException extends Exception {
    /**
     * @param QMVariable $tag
     * @param QMVariable $tagged
     */
    public function __construct(QMVariable $tag, QMVariable $tagged){
        $tagCategory = $tag->getVariableCategoryName();
        $taggedCategory = $tagged->getVariableCategoryName();
        $message =
            "Tag $tag->name category $tagCategory does not match tagged $tagged->name category $taggedCategory. \n";
        $message .= "$tag variable was created ".$tag->getCreatedAt()." \n";
        $message .= "$tagged variable was created ".$tagged->getCreatedAt()." \n";
        $message .= "Your Options: "." \n";
        $message .= " 1. Delete the tag at : "." \n";
        $message .= $tag->getVariableSettingsUrl()." \n";
        $message .= $tagged->getVariableSettingsUrl()." \n";
        $message .= " 2. Add the variable categories $tagCategory and $taggedCategory to acceptableTagTaggedPairs in \App\Variables\CommonTag::validateTagCategories";
        parent::__construct($message, QMException::CODE_BAD_REQUEST);
    }
}
