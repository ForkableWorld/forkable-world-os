<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Intents;
use App\VariableCategories\EmotionsVariableCategory;
use App\VariableCategories\FoodsVariableCategory;
use App\VariableCategories\SymptomsVariableCategory;
use App\VariableCategories\TreatmentsVariableCategory;

class DoneWithCategoryIntent extends QMIntent {
    public $actionName = 'done_with_category';
    public $triggerPhrases = [
        "done",
        "done",
        "done with",
        "done adding"
    ];
    public $variableCategoryName;
    public function __construct(){
        parent::__construct();
    }
    /**
     * @return mixed|void
     */
    public function fulfillIntent(){
        $this->saidDoneWithCategory(EmotionsVariableCategory::NAME);
        $this->saidDoneWithCategory(TreatmentsVariableCategory::NAME);
        $this->saidDoneWithCategory(SymptomsVariableCategory::NAME);
        $this->saidDoneWithCategory(FoodsVariableCategory::NAME);
    }
}
